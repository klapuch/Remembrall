--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.3
-- Dumped by pg_dump version 9.6.3

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: assert; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA assert;


ALTER SCHEMA assert OWNER TO postgres;

--
-- Name: unit_tests; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA unit_tests;


ALTER SCHEMA unit_tests OWNER TO postgres;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: citext; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;


--
-- Name: EXTENSION citext; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION citext IS 'data type for case-insensitive character strings';


SET search_path = public, pg_catalog;

--
-- Name: languages; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE languages AS ENUM (
    'xpath',
    'css'
);


ALTER TYPE languages OWNER TO postgres;

--
-- Name: expression; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE expression AS (
	value character varying,
	language languages
);


ALTER TYPE expression OWNER TO postgres;

--
-- Name: test_result; Type: DOMAIN; Schema: public; Owner: postgres
--

CREATE DOMAIN test_result AS text;


ALTER DOMAIN test_result OWNER TO postgres;

SET search_path = assert, pg_catalog;

--
-- Name: are_equal(anyarray); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION are_equal(VARIADIC anyarray, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
DECLARE count integer=0;
	DECLARE total_items bigint;
	DECLARE total_rows bigint;
BEGIN
	result := false;

	WITH counter
	AS
	(
		SELECT *
		FROM explode_array($1) AS items
	)
	SELECT
		COUNT(items),
		COUNT(*)
	INTO
		total_items,
		total_rows
	FROM counter;

	IF(total_items = 0 OR total_items = total_rows) THEN
		result := true;
	END IF;

	IF(result AND total_items > 0) THEN
		SELECT COUNT(DISTINCT $1[s.i])
		INTO count
		FROM generate_series(array_lower($1,1), array_upper($1,1)) AS s(i)
		ORDER BY 1;

		IF count <> 1 THEN
			result := FALSE;
		END IF;
	END IF;

	IF(NOT result) THEN
		message := 'ASSERT ARE_EQUAL FAILED.';
		PERFORM assert.fail(message);
		RETURN;
	END IF;

	message := 'Asserts are equal.';
	PERFORM assert.ok(message);
	result := true;
	RETURN;
END
$_$;


ALTER FUNCTION assert.are_equal(VARIADIC anyarray, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: are_not_equal(anyarray); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION are_not_equal(VARIADIC anyarray, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
DECLARE count integer=0;
	DECLARE count_nulls bigint;
BEGIN
	SELECT COUNT(*)
	INTO count_nulls
	FROM explode_array($1) AS items
	WHERE items IS NULL;

	SELECT COUNT(DISTINCT $1[s.i]) INTO count
	FROM generate_series(array_lower($1,1), array_upper($1,1)) AS s(i)
	ORDER BY 1;

	IF(count + count_nulls <> array_upper($1,1) OR count_nulls > 1) THEN
		message := 'ASSERT ARE_NOT_EQUAL FAILED.';
		PERFORM assert.fail(message);
		RESULT := FALSE;
		RETURN;
	END IF;

	message := 'Asserts are not equal.';
	PERFORM assert.ok(message);
	result := true;
	RETURN;
END
$_$;


ALTER FUNCTION assert.are_not_equal(VARIADIC anyarray, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: fail(text); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION fail(message text) RETURNS text
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $_$
BEGIN
	IF $1 IS NULL OR trim($1) = '' THEN
		message := 'NO REASON SPECIFIED';
	END IF;

	RAISE WARNING 'ASSERT FAILED : %', message;
	RETURN message;
END
$_$;


ALTER FUNCTION assert.fail(message text) OWNER TO postgres;

--
-- Name: function_exists(text); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION function_exists(function_name text, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql
    AS $_$
BEGIN
	IF NOT EXISTS
	(
		SELECT  1
		FROM    pg_catalog.pg_namespace n
		JOIN    pg_catalog.pg_proc p
		ON      pronamespace = n.oid
		WHERE replace(nspname || '.' || proname || '(' || oidvectortypes(proargtypes) || ')', ' ' , '')::text=$1
	) THEN
		message := format('The function %s does not exist.', $1);
		PERFORM assert.fail(message);

		result := false;
		RETURN;
	END IF;

	message := format('Ok. The function %s exists.', $1);
	PERFORM assert.ok(message);
	result := true;
	RETURN;
END
$_$;


ALTER FUNCTION assert.function_exists(function_name text, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: if_functions_compile(text[]); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION if_functions_compile(VARIADIC _schema_name text[], OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql
    AS $_$
DECLARE all_parameters              text;
	DECLARE current_function            RECORD;
	DECLARE current_function_name       text;
	DECLARE current_type                text;
	DECLARE current_type_schema         text;
	DECLARE current_parameter           text;
	DECLARE functions_count             smallint := 0;
	DECLARE current_parameters_count    int;
	DECLARE i                           int;
	DECLARE command_text                text;
	DECLARE failed_functions            text;
BEGIN
	FOR current_function IN
	SELECT proname, proargtypes, nspname
	FROM pg_proc
	INNER JOIN pg_namespace
	ON pg_proc.pronamespace = pg_namespace.oid
	WHERE pronamespace IN
		  (
			  SELECT oid FROM pg_namespace
			  WHERE nspname = ANY($1)
					AND nspname NOT IN
						(
							'assert', 'unit_tests', 'information_schema'
						)
					AND proname NOT IN('if_functions_compile')
		  )
	LOOP
		current_parameters_count := array_upper(current_function.proargtypes, 1) + 1;

		i := 0;
		all_parameters := '';

		LOOP
			IF i < current_parameters_count THEN
				IF i > 0 THEN
					all_parameters := all_parameters || ', ';
				END IF;

				SELECT
					nspname, typname
				INTO
					current_type_schema, current_type
				FROM pg_type
				INNER JOIN pg_namespace
				ON pg_type.typnamespace = pg_namespace.oid
				WHERE pg_type.oid = current_function.proargtypes[i];

				IF(current_type IN('int4', 'int8', 'numeric', 'integer_strict', 'money_strict','decimal_strict', 'integer_strict2', 'money_strict2','decimal_strict2', 'money','decimal', 'numeric', 'bigint')) THEN
					current_parameter := '1::' || current_type_schema || '.' || current_type;
				ELSIF(substring(current_type, 1, 1) = '_') THEN
					current_parameter := 'NULL::' || current_type_schema || '.' || substring(current_type, 2, length(current_type)) || '[]';
				ELSIF(current_type in ('date')) THEN
					current_parameter := '''1-1-2000''::' || current_type;
				ELSIF(current_type = 'bool') THEN
					current_parameter := 'false';
				ELSE
					current_parameter := '''''::' || quote_ident(current_type_schema) || '.' || quote_ident(current_type);
				END IF;

				all_parameters = all_parameters || current_parameter;

				i := i + 1;
			ELSE
				EXIT;
			END IF;
		END LOOP;

		BEGIN
			current_function_name := quote_ident(current_function.nspname)  || '.' || quote_ident(current_function.proname);
			command_text := 'SELECT * FROM ' || current_function_name || '(' || all_parameters || ');';

			EXECUTE command_text;
			functions_count := functions_count + 1;

			EXCEPTION WHEN OTHERS THEN
			IF(failed_functions IS NULL) THEN
				failed_functions := '';
			END IF;

			IF(SQLSTATE IN('42702', '42704')) THEN
				failed_functions := failed_functions || E'\n' || command_text || E'\n' || SQLERRM || E'\n';
			END IF;
		END;


	END LOOP;

	IF(failed_functions != '') THEN
		message := E'The test if_functions_compile failed. The following functions failed to compile : \n\n' || failed_functions;
		result := false;
		PERFORM assert.fail(message);
		RETURN;
	END IF;
END;
$_$;


ALTER FUNCTION assert.if_functions_compile(VARIADIC _schema_name text[], OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: if_views_compile(text[]); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION if_views_compile(VARIADIC _schema_name text[], OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql
    AS $_$

DECLARE message                     test_result;
	DECLARE current_view                RECORD;
	DECLARE current_view_name           text;
	DECLARE command_text                text;
	DECLARE failed_views                text;
BEGIN
	FOR current_view IN
	SELECT table_name, table_schema
	FROM information_schema.views
	WHERE table_schema = ANY($1)
	LOOP

		BEGIN
			current_view_name := quote_ident(current_view.table_schema)  || '.' || quote_ident(current_view.table_name);
			command_text := 'SELECT * FROM ' || current_view_name || ' LIMIT 1;';

			RAISE NOTICE '%', command_text;

			EXECUTE command_text;

			EXCEPTION WHEN OTHERS THEN
			IF(failed_views IS NULL) THEN
				failed_views := '';
			END IF;

			failed_views := failed_views || E'\n' || command_text || E'\n' || SQLERRM || E'\n';
		END;


	END LOOP;

	IF(failed_views != '') THEN
		message := E'The test if_views_compile failed. The following views failed to compile : \n\n' || failed_views;
		result := false;
		PERFORM assert.fail(message);
		RETURN;
	END IF;

	RETURN;
END;
$_$;


ALTER FUNCTION assert.if_views_compile(VARIADIC _schema_name text[], OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: is_equal(anyelement, anyelement); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION is_equal(have anyelement, want anyelement, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF($1 IS NOT DISTINCT FROM $2) THEN
		message := 'Assert is equal.';
		PERFORM assert.ok(message);
		result := true;
		RETURN;
	END IF;

	message := E'ASSERT IS_EQUAL FAILED.\n\nHave -> ' || COALESCE($1::text, 'NULL') || E'\nWant -> ' || COALESCE($2::text, 'NULL') || E'\n';
	PERFORM assert.fail(message);
	result := false;
	RETURN;
END
$_$;


ALTER FUNCTION assert.is_equal(have anyelement, want anyelement, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: is_false(boolean); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION is_false(boolean, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF(NOT $1) THEN
		message := 'Assert is false.';
		PERFORM assert.ok(message);
		result := true;
		RETURN;
	END IF;

	message := E'ASSERT IS_FALSE FAILED. A false condition was expected.\n\n\n';
	PERFORM assert.fail(message);
	result := false;
	RETURN;
END
$_$;


ALTER FUNCTION assert.is_false(boolean, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: is_greater_than(anyelement, anyelement); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION is_greater_than(x anyelement, y anyelement, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF($1 > $2) THEN
		message := 'Assert greater than condition is satisfied.';
		PERFORM assert.ok(message);
		result := true;
		RETURN;
	END IF;

	message := E'ASSERT IS_GREATER_THAN FAILED.\n\n X : -> ' || COALESCE($1::text, 'NULL') || E'\n is not greater than Y:   -> ' || COALESCE($2::text, 'NULL') || E'\n';
	PERFORM assert.fail(message);
	result := false;
	RETURN;
END
$_$;


ALTER FUNCTION assert.is_greater_than(x anyelement, y anyelement, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: is_less_than(anyelement, anyelement); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION is_less_than(x anyelement, y anyelement, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF($1 < $2) THEN
		message := 'Assert less than condition is satisfied.';
		PERFORM assert.ok(message);
		result := true;
		RETURN;
	END IF;

	message := E'ASSERT IS_LESS_THAN FAILED.\n\n X : -> ' || COALESCE($1::text, 'NULL') || E'\n is not less than Y:   -> ' || COALESCE($2::text, 'NULL') || E'\n';
	PERFORM assert.fail(message);
	result := false;
	RETURN;
END
$_$;


ALTER FUNCTION assert.is_less_than(x anyelement, y anyelement, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: is_not_equal(anyelement, anyelement); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION is_not_equal(already_have anyelement, dont_want anyelement, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF($1 IS DISTINCT FROM $2) THEN
		message := 'Assert is not equal.';
		PERFORM assert.ok(message);
		result := true;
		RETURN;
	END IF;

	message := E'ASSERT IS_NOT_EQUAL FAILED.\n\nAlready Have -> ' || COALESCE($1::text, 'NULL') || E'\nDon''t Want   -> ' || COALESCE($2::text, 'NULL') || E'\n';
	PERFORM assert.fail(message);
	result := false;
	RETURN;
END
$_$;


ALTER FUNCTION assert.is_not_equal(already_have anyelement, dont_want anyelement, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: is_not_null(anyelement); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION is_not_null(anyelement, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF($1 IS NOT NULL) THEN
		message := 'Assert is not NULL.';
		PERFORM assert.ok(message);
		result := true;
		RETURN;
	END IF;

	message := E'ASSERT IS_NOT_NULL FAILED. The value is NULL.\n\n\n';
	PERFORM assert.fail(message);
	result := false;
	RETURN;
END
$_$;


ALTER FUNCTION assert.is_not_null(anyelement, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: is_null(anyelement); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION is_null(anyelement, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF($1 IS NULL) THEN
		message := 'Assert is NULL.';
		PERFORM assert.ok(message);
		result := true;
		RETURN;
	END IF;

	message := E'ASSERT IS_NULL FAILED. NULL value was expected.\n\n\n';
	PERFORM assert.fail(message);
	result := false;
	RETURN;
END
$_$;


ALTER FUNCTION assert.is_null(anyelement, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: is_true(boolean); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION is_true(boolean, OUT message text, OUT result boolean) RETURNS record
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF($1) THEN
		message := 'Assert is true.';
		PERFORM assert.ok(message);
		result := true;
		RETURN;
	END IF;

	message := E'ASSERT IS_TRUE FAILED. A true condition was expected.\n\n\n';
	PERFORM assert.fail(message);
	result := false;
	RETURN;
END
$_$;


ALTER FUNCTION assert.is_true(boolean, OUT message text, OUT result boolean) OWNER TO postgres;

--
-- Name: ok(text); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION ok(message text) RETURNS text
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $$
BEGIN
	RAISE NOTICE 'OK : %', message;
	RETURN '';
END
$$;


ALTER FUNCTION assert.ok(message text) OWNER TO postgres;

--
-- Name: pass(text); Type: FUNCTION; Schema: assert; Owner: postgres
--

CREATE FUNCTION pass(message text) RETURNS text
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $$
BEGIN
	RAISE NOTICE 'ASSERT PASSED : %', message;
	RETURN '';
END
$$;


ALTER FUNCTION assert.pass(message text) OWNER TO postgres;

SET search_path = public, pg_catalog;

--
-- Name: counted_subscriptions(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION counted_subscriptions(OUT part_id integer, OUT occurrences bigint) RETURNS SETOF record
    LANGUAGE sql
    AS $$
  WITH merged_subscriptions AS (
    SELECT part_id
    FROM subscriptions
    UNION ALL
    SELECT part_id
    FROM participants
    INNER JOIN subscriptions ON subscriptions.id = participants.subscription_id
    WHERE accepted = TRUE
  )
  SELECT part_id, COUNT(*) AS occurrences
  FROM merged_subscriptions
  GROUP BY part_id;
$$;


ALTER FUNCTION public.counted_subscriptions(OUT part_id integer, OUT occurrences bigint) OWNER TO postgres;

--
-- Name: notify_subscriptions(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION notify_subscriptions() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
			begin
				INSERT INTO notifications (subscription_id, notified_at) VALUES (new.id, NOW());
				return new;
			end
			$$;


ALTER FUNCTION public.notify_subscriptions() OWNER TO postgres;

--
-- Name: readable_subscriptions(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION readable_subscriptions() RETURNS TABLE(id integer, user_id integer, part_id integer, "interval" interval, last_update timestamp with time zone, snapshot character varying, interval_seconds integer)
    LANGUAGE sql
    AS $$
SET intervalstyle = 'ISO_8601';
SELECT *, extract(epoch from interval)::integer AS interval_seconds
   FROM subscriptions;

			$$;

ALTER FUNCTION public.readable_subscriptions() OWNER TO postgres;


--
-- Name: to_ISO8601(); Type: FUNCTION; Schema: public; Owner: postgres
--
CREATE FUNCTION to_ISO8601(timestamptz) RETURNS text
LANGUAGE plpgsql IMMUTABLE STRICT
AS $$
BEGIN
	RETURN to_char ($1, 'YYYY-MM-DD"T"HH24:MI:SS"Z"');
END
$$;

ALTER FUNCTION public.to_ISO8601() OWNER TO postgres;


--
-- Name: record_invitation(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION record_invitation() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
    INSERT INTO invitation_attempts (participant_id, attempt_at) VALUES (NEW.id, NOW());
    return new;
end
$$;


ALTER FUNCTION public.record_invitation() OWNER TO postgres;

--
-- Name: record_page_access(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION record_page_access() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
    INSERT INTO page_visits (page_url, visited_at) VALUES (NEW.url, NOW());
    return new;
end
$$;


ALTER FUNCTION public.record_page_access() OWNER TO postgres;

--
-- Name: record_part_access(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION record_part_access() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
    INSERT INTO part_visits (part_id, visited_at) VALUES (NEW.id, NOW());
    return new;
end
$$;


ALTER FUNCTION public.record_part_access() OWNER TO postgres;

--
-- Name: restart_sequences(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION restart_sequences() RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
    statements CURSOR FOR
    	SELECT 'ALTER SEQUENCE ' || relname || ' RESTART;' AS query
      FROM pg_class
      WHERE relkind = 'S'
	  AND relnamespace = 2200;
BEGIN
    FOR stmt IN statements LOOP
        EXECUTE stmt.query;
    END LOOP;
END;
$$;


ALTER FUNCTION public.restart_sequences() OWNER TO postgres;

--
-- Name: truncate_tables(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION truncate_tables(username character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
    statements CURSOR FOR
        SELECT tablename FROM pg_tables
        WHERE tableowner = username AND schemaname = 'public';
BEGIN
    FOR stmt IN statements LOOP
        EXECUTE 'TRUNCATE TABLE ' || quote_ident(stmt.tablename) || ' CASCADE;';
    END LOOP;
END;
$$;


ALTER FUNCTION public.truncate_tables(username character varying) OWNER TO postgres;

SET search_path = unit_tests, pg_catalog;

--
-- Name: add_dependency(text, text); Type: FUNCTION; Schema: unit_tests; Owner: postgres
--

CREATE FUNCTION add_dependency(p_dependent text, p_depends_on text) RETURNS void
    LANGUAGE plpgsql STRICT
    AS $$
DECLARE dependent_ns text;
	DECLARE dependent_name text;
	DECLARE depends_on_ns text;
	DECLARE depends_on_name text;
	DECLARE arr text[];
BEGIN
	IF p_dependent LIKE '%.%' THEN
		SELECT regexp_split_to_array(p_dependent, E'\\.') INTO arr;
		SELECT arr[1] INTO dependent_ns;
		SELECT arr[2] INTO dependent_name;
	ELSE
		SELECT NULL INTO dependent_ns;
		SELECT p_dependent INTO dependent_name;
	END IF;
	IF p_depends_on LIKE '%.%' THEN
		SELECT regexp_split_to_array(p_depends_on, E'\\.') INTO arr;
		SELECT arr[1] INTO depends_on_ns;
		SELECT arr[2] INTO depends_on_name;
	ELSE
		SELECT NULL INTO depends_on_ns;
		SELECT p_depends_on INTO depends_on_name;
	END IF;
	INSERT INTO unit_tests.dependencies (dependent_ns, dependent_function_name, depends_on_ns, depends_on_function_name)
	VALUES (dependent_ns, dependent_name, depends_on_ns, depends_on_name);
END
$$;


ALTER FUNCTION unit_tests.add_dependency(p_dependent text, p_depends_on text) OWNER TO postgres;

--
-- Name: begin(integer, text); Type: FUNCTION; Schema: unit_tests; Owner: postgres
--

CREATE FUNCTION begin(verbosity integer DEFAULT 9, format text DEFAULT ''::text) RETURNS TABLE(message text, result character)
    LANGUAGE plpgsql
    AS $_$
DECLARE this                    record;
	DECLARE _function_name          text;
	DECLARE _sql                    text;
	DECLARE _failed_dependencies    text[];
	DECLARE _num_of_test_functions  integer;
	DECLARE _should_skip            boolean;
	DECLARE _message                text;
	DECLARE _error                  text;
	DECLARE _context                text;
	DECLARE _result                 character(1);
	DECLARE _test_id                integer;
	DECLARE _status                 boolean;
	DECLARE _total_tests            integer                         = 0;
	DECLARE _failed_tests           integer                         = 0;
	DECLARE _skipped_tests          integer                         = 0;
	DECLARE _list_of_failed_tests   text;
	DECLARE _list_of_skipped_tests  text;
	DECLARE _started_from           TIMESTAMP WITHOUT TIME ZONE;
	DECLARE _completed_on           TIMESTAMP WITHOUT TIME ZONE;
	DECLARE _delta                  integer;
	DECLARE _ret_val                text                            = '';
	DECLARE _verbosity              text[]                          =
ARRAY['debug5', 'debug4', 'debug3', 'debug2', 'debug1', 'log', 'notice', 'warning', 'error', 'fatal', 'panic'];
BEGIN
	_started_from := clock_timestamp() AT TIME ZONE 'UTC';

	IF(format='teamcity') THEN
		RAISE INFO '##teamcity[testSuiteStarted name=''Plpgunit'' message=''Test started from : %'']', _started_from;
	ELSE
		RAISE INFO 'Test started from : %', _started_from;
	END IF;

	IF($1 > 11) THEN
		$1 := 9;
	END IF;

	EXECUTE 'SET CLIENT_MIN_MESSAGES TO ' || _verbosity[$1];
	RAISE WARNING 'CLIENT_MIN_MESSAGES set to : %' , _verbosity[$1];

	SELECT nextval('unit_tests.tests_test_id_seq') INTO _test_id;

	INSERT INTO unit_tests.tests(test_id)
		SELECT _test_id;

	DROP TABLE IF EXISTS temp_test_functions;
	CREATE TEMP TABLE temp_test_functions AS
		SELECT
			nspname AS ns_name,
			proname AS function_name,
			p.oid as oid
		FROM    pg_catalog.pg_namespace n
		JOIN    pg_catalog.pg_proc p
		ON      pronamespace = n.oid
		WHERE
			prorettype='test_result'::regtype::oid;

	SELECT count(*) INTO _num_of_test_functions FROM temp_test_functions;

	DROP TABLE IF EXISTS temp_dependency_levels;
	CREATE TEMP TABLE temp_dependency_levels AS
		WITH RECURSIVE dependency_levels(ns_name, function_name, oid, level) AS (
			-- select functions without any dependencies
			SELECT ns_name, function_name, tf.oid, 0 as level
			FROM temp_test_functions tf
			LEFT OUTER JOIN unit_tests.dependencies d ON tf.ns_name = d.dependent_ns AND tf.function_name = d.dependent_function_name
			WHERE d.dependency_id IS NULL
			UNION
			-- add functions which depend on the previous level functions
			SELECT d.dependent_ns, d.dependent_function_name, tf.oid, level + 1
			FROM dependency_levels dl
			JOIN unit_tests.dependencies d ON dl.ns_name = d.depends_on_ns AND dl.function_name LIKE d.depends_on_function_name
			JOIN temp_test_functions tf ON d.dependent_ns = tf.ns_name AND d.dependent_function_name = tf.function_name
			WHERE level < _num_of_test_functions -- don't follow circles for too long
		)
		SELECT ns_name, function_name, oid, max(level) as max_level
		FROM dependency_levels
		GROUP BY ns_name, function_name, oid;

	IF (SELECT count(*) < _num_of_test_functions FROM temp_dependency_levels) THEN
		SELECT array_to_string(array_agg(tf.ns_name || '.' || tf.function_name || '()'), ', ')
		INTO _error
		FROM temp_test_functions tf
		LEFT OUTER JOIN temp_dependency_levels dl ON tf.oid = dl.oid
		WHERE dl.oid IS NULL;
		RAISE EXCEPTION 'Cyclic dependencies detected. Check the following test functions: %', _error;
	END IF;

	IF exists(SELECT * FROM temp_dependency_levels WHERE max_level = _num_of_test_functions) THEN
		SELECT array_to_string(array_agg(ns_name || '.' || function_name || '()'), ', ')
		INTO _error
		FROM temp_dependency_levels
		WHERE max_level = _num_of_test_functions;
		RAISE EXCEPTION 'Cyclic dependencies detected. Check the dependency graph including following test functions: %', _error;
	END IF;

	FOR this IN
	SELECT ns_name, function_name, max_level
	FROM temp_dependency_levels
	ORDER BY max_level, oid
	LOOP
		BEGIN
			_status := false;
			_total_tests := _total_tests + 1;

			_function_name = this.ns_name|| '.' || this.function_name || '()';

			SELECT array_agg(td.function_name)
			INTO _failed_dependencies
			FROM unit_tests.dependencies d
			JOIN unit_tests.test_details td on td.function_name LIKE d.depends_on_ns || '.' || d.depends_on_function_name || '()'
			WHERE d.dependent_ns = this.ns_name AND d.dependent_function_name = this.function_name
				  AND test_id = _test_id AND status = false;

			SELECT _failed_dependencies IS NOT NULL INTO _should_skip;
			IF NOT _should_skip THEN
				_sql := 'SELECT ' || _function_name || ';';

				RAISE NOTICE 'RUNNING TEST : %.', _function_name;

				IF(format='teamcity') THEN
					RAISE INFO '##teamcity[testStarted name=''%'' message=''%'']', _function_name, _started_from;
				ELSE
					RAISE INFO 'Running test % : %', _function_name, _started_from;
				END IF;

				EXECUTE _sql INTO _message;

				IF _message = '' THEN
					_status := true;

					IF(format='teamcity') THEN
						RAISE INFO '##teamcity[testFinished name=''%'' message=''%'']', _function_name, clock_timestamp() AT TIME ZONE 'UTC';
					ELSE
						RAISE INFO 'Passed % : %', _function_name, clock_timestamp() AT TIME ZONE 'UTC';
					END IF;
				ELSE
					IF(format='teamcity') THEN
						RAISE INFO '##teamcity[testFailed name=''%'' message=''%'']', _function_name, _message;
						RAISE INFO '##teamcity[testFinished name=''%'' message=''%'']', _function_name, clock_timestamp() AT TIME ZONE 'UTC';
					ELSE
						RAISE INFO 'Test failed % : %', _function_name, _message;
					END IF;
				END IF;
			ELSE
				-- skipped test
				_status := true;
				_message = 'Failed dependencies: ' || array_to_string(_failed_dependencies, ',');
				IF(format='teamcity') THEN
					RAISE INFO '##teamcity[testSkipped name=''%''] : %', _function_name, clock_timestamp() AT TIME ZONE 'UTC';
				ELSE
					RAISE INFO 'Skipped % : %', _function_name, clock_timestamp() AT TIME ZONE 'UTC';
				END IF;
			END IF;

			INSERT INTO unit_tests.test_details(test_id, function_name, message, status, executed, ts)
				SELECT _test_id, _function_name, _message, _status, NOT _should_skip, clock_timestamp();

			IF NOT _status THEN
				_failed_tests := _failed_tests + 1;
				RAISE WARNING 'TEST % FAILED.', _function_name;
				RAISE WARNING 'REASON: %', _message;
			ELSIF NOT _should_skip THEN
				RAISE NOTICE 'TEST % COMPLETED WITHOUT ERRORS.', _function_name;
			ELSE
				_skipped_tests := _skipped_tests + 1;
				RAISE WARNING 'TEST % SKIPPED, BECAUSE A DEPENDENCY FAILED.', _function_name;
			END IF;

			EXCEPTION WHEN OTHERS THEN
			GET STACKED DIAGNOSTICS _context = PG_EXCEPTION_CONTEXT;
			_message := 'ERR: [' || SQLSTATE || ']: ' || SQLERRM || E'\n    ' || split_part(_context, E'\n', 1);
			INSERT INTO unit_tests.test_details(test_id, function_name, message, status, executed)
				SELECT _test_id, _function_name, _message, false, true;

			_failed_tests := _failed_tests + 1;

			RAISE WARNING 'TEST % FAILED.', _function_name;
			RAISE WARNING 'REASON: %', _message;

			IF(format='teamcity') THEN
				RAISE INFO '##teamcity[testFailed name=''%'' message=''%'']', _function_name, _message;
				RAISE INFO '##teamcity[testFinished name=''%'' message=''%'']', _function_name, clock_timestamp() AT TIME ZONE 'UTC';
			ELSE
				RAISE INFO 'Test failed % : %', _function_name, _message;
			END IF;
		END;
	END LOOP;

	_completed_on := clock_timestamp() AT TIME ZONE 'UTC';
	_delta := extract(millisecond from _completed_on - _started_from)::integer;

	UPDATE unit_tests.tests
	SET total_tests = _total_tests, failed_tests = _failed_tests, skipped_tests = _skipped_tests, completed_on = _completed_on
	WHERE test_id = _test_id;

	IF format='junit' THEN
		SELECT
			'<?xml version="1.0" encoding="UTF-8"?>'||
			xmlelement
			(
				name testsuites,
				xmlelement
				(
					name                    testsuite,
					xmlattributes
					(
					'plpgunit'          AS name,
					t.total_tests       AS tests,
					t.failed_tests      AS failures,
					0                   AS errors,
					EXTRACT
					(
						EPOCH FROM t.completed_on - t.started_on
					)                   AS time
					),
					xmlagg
					(
						xmlelement
						(
							name testcase,
							xmlattributes
							(
							td.function_name
							AS name,
							EXTRACT
							(
								EPOCH FROM td.ts - t.started_on
							)           AS time
							),
							CASE
							WHEN td.status=false
								THEN
									xmlelement
									(
										name failure,
										td.message
									)
							END
						)
					)
				)
			) INTO _ret_val
		FROM unit_tests.test_details td, unit_tests.tests t
		WHERE
			t.test_id=_test_id
			AND
			td.test_id=t.test_id
		GROUP BY t.test_id;
	ELSE
		WITH failed_tests AS
		(
			SELECT row_number() OVER (ORDER BY id) AS id,
				unit_tests.test_details.function_name,
				unit_tests.test_details.message
			FROM unit_tests.test_details
			WHERE test_id = _test_id
				  AND status= false
		)
		SELECT array_to_string(array_agg(f.id::text || '. ' || f.function_name || ' --> ' || f.message), E'\n') INTO _list_of_failed_tests
		FROM failed_tests f;

		WITH skipped_tests AS
		(
			SELECT row_number() OVER (ORDER BY id) AS id,
				unit_tests.test_details.function_name,
				unit_tests.test_details.message
			FROM unit_tests.test_details
			WHERE test_id = _test_id
				  AND executed = false
		)
		SELECT array_to_string(array_agg(s.id::text || '. ' || s.function_name || ' --> ' || s.message), E'\n') INTO _list_of_skipped_tests
		FROM skipped_tests s;

		_ret_val := _ret_val ||  'Test completed on : ' || _completed_on::text || E' UTC. \nTotal test runtime: ' || _delta::text || E' ms.\n';
		_ret_val := _ret_val || E'\nTotal tests run : ' || COALESCE(_total_tests, '0')::text;
		_ret_val := _ret_val || E'.\nPassed tests    : ' || (COALESCE(_total_tests, '0') - COALESCE(_failed_tests, '0') - COALESCE(_skipped_tests, '0'))::text;
		_ret_val := _ret_val || E'.\nFailed tests    : ' || COALESCE(_failed_tests, '0')::text;
		_ret_val := _ret_val || E'.\nSkipped tests   : ' || COALESCE(_skipped_tests, '0')::text;
		_ret_val := _ret_val || E'.\n\nList of failed tests:\n' || '----------------------';
		_ret_val := _ret_val || E'\n' || COALESCE(_list_of_failed_tests, '<NULL>')::text;
		_ret_val := _ret_val || E'.\n\nList of skipped tests:\n' || '----------------------';
		_ret_val := _ret_val || E'\n' || COALESCE(_list_of_skipped_tests, '<NULL>')::text;
		_ret_val := _ret_val || E'\n' || E'End of plpgunit test.\n\n';
	END IF;

	IF _failed_tests > 0 THEN
		_result := 'N';

		IF(format='teamcity') THEN
			RAISE INFO '##teamcity[testStarted name=''Result'']';
			RAISE INFO '##teamcity[testFailed name=''Result'' message=''%'']', REPLACE(_ret_val, E'\n', ' |n');
			RAISE INFO '##teamcity[testFinished name=''Result'']';
			RAISE INFO '##teamcity[testSuiteFinished name=''Plpgunit'' message=''%'']', REPLACE(_ret_val, E'\n', '|n');
		ELSE
			RAISE INFO '%', _ret_val;
		END IF;
	ELSE
		_result := 'Y';

		IF(format='teamcity') THEN
			RAISE INFO '##teamcity[testSuiteFinished name=''Plpgunit'' message=''%'']', REPLACE(_ret_val, E'\n', '|n');
		ELSE
			RAISE INFO '%', _ret_val;
		END IF;
	END IF;

	SET CLIENT_MIN_MESSAGES TO notice;

	RETURN QUERY SELECT _ret_val, _result;
END
$_$;


ALTER FUNCTION unit_tests.begin(verbosity integer, format text) OWNER TO postgres;

--
-- Name: begin_junit(integer); Type: FUNCTION; Schema: unit_tests; Owner: postgres
--

CREATE FUNCTION begin_junit(verbosity integer DEFAULT 9) RETURNS TABLE(message text, result character)
    LANGUAGE plpgsql
    AS $_$
BEGIN
	RETURN QUERY
	SELECT * FROM unit_tests.begin($1, 'junit');
END
$_$;


ALTER FUNCTION unit_tests.begin_junit(verbosity integer) OWNER TO postgres;

--
-- Name: begin_psql(integer, text); Type: FUNCTION; Schema: unit_tests; Owner: postgres
--

CREATE FUNCTION begin_psql(verbosity integer DEFAULT 9, format text DEFAULT ''::text) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
	_msg text;
	_res character(1);
BEGIN
	SELECT * INTO _msg, _res
	FROM unit_tests.begin(verbosity, format)
	;
	IF(_res != 'Y') THEN
		RAISE EXCEPTION 'Tests failed [%]', _msg;
	END IF;
END
$$;


ALTER FUNCTION unit_tests.begin_psql(verbosity integer, format text) OWNER TO postgres;

--
-- Name: counted_subscriptions(); Type: FUNCTION; Schema: unit_tests; Owner: postgres
--

CREATE FUNCTION counted_subscriptions() RETURNS public.test_result
    LANGUAGE plpgsql
    AS $$
DECLARE
	message TEST_RESULT;
	count INTEGER;
	expected_count CONSTANT INTEGER := 3;
BEGIN
	PERFORM truncate_tables('postgres');
	PERFORM restart_sequences();

	INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
	(2, 'bar.cz', ROW ('//bar', 'xpath'), 'bar', 'barSnap'),
	(3, 'baz.cz', ROW ('//baz', 'xpath'), 'baz', 'bazSnap');

	INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
	(1, 2, 'PT6M', NOW(), md5(random()::TEXT)), (2, 2, 'PT6M', NOW(), md5(random()::TEXT));

	INSERT INTO participants (id, email, subscription_id, code, invited_at, accepted, decided_at) VALUES (1, 'a@a.cz', 1, 'abc', NOW(), TRUE, NOW()),
	(2, 'b@a.cz', 1, 'abc', NOW(), FALSE, NOW()),
	(3, 'c@a.cz', 1, 'abc', NOW(), FALSE, NULL);

	SELECT occurrences
	FROM public.counted_subscriptions()
	INTO count;
	IF count = expected_count
	THEN
		SELECT assert.ok('Counted subscriptions are matching.')
		INTO message;
	ELSE
		SELECT assert.fail(format('Expected count of subscription was %s, actual %s', expected_count, count))
		INTO message;
	END IF;
	RETURN message;
END
$$;


ALTER FUNCTION unit_tests.counted_subscriptions() OWNER TO postgres;

--
-- Name: readable_subscriptions(); Type: FUNCTION; Schema: unit_tests; Owner: postgres
--

CREATE FUNCTION readable_subscriptions() RETURNS public.test_result
    LANGUAGE plpgsql
    AS $$
DECLARE
	message TEST_RESULT;
	second_intervals INTEGER[];
	iso_intervals VARCHAR[];
	expected_second_intervals CONSTANT INTEGER[] := ARRAY[1, 20, 0, 555, 123];
	expected_iso_intervals CONSTANT VARCHAR[] := ARRAY['PT1S', 'PT20S', 'PT0S', 'PT9M15S', 'PT2M3S'];
BEGIN
	PERFORM truncate_tables('postgres');
	PERFORM restart_sequences();

	INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
	(1, 2, 'PT1S', NOW(), md5(random()::TEXT)),
	(1, 3, 'PT20S', NOW(), md5(random()::TEXT)),
	(1, 5, 'PT0S', NOW(), md5(random()::TEXT)),
	(2, 6, 'PT555S', NOW(), md5(random()::TEXT)),
	(2, 7, 'PT2M3S', NOW(), md5(random()::TEXT));

	SELECT array_agg(interval_seconds), array_agg(interval)
	FROM readable_subscriptions()
	INTO second_intervals, iso_intervals;
	IF second_intervals = expected_second_intervals
	THEN
		SELECT assert.ok('Second intervals are matching.')
		INTO message;
	ELSE
		SELECT assert.fail(format('Expected intervals in seconds were %s, actual %s', expected_second_intervals, second_intervals))
		INTO message;
	END IF;
	IF iso_intervals = expected_iso_intervals
	THEN
		SELECT assert.ok('ISO intervals are matching.')
		INTO message;
	ELSE
		SELECT assert.fail(format('Expected ISO intervals were %s, actual %s', expected_iso_intervals, iso_intervals))
		INTO message;
	END IF;
	RETURN message;
END
$$;


ALTER FUNCTION unit_tests.readable_subscriptions() OWNER TO postgres;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: forgotten_passwords; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE forgotten_passwords (
    id integer NOT NULL,
    user_id integer NOT NULL,
    reminder character varying(141) NOT NULL,
    used boolean NOT NULL,
    reminded_at timestamp with time zone NOT NULL,
    expire_at timestamp with time zone NOT NULL
);


ALTER TABLE forgotten_passwords OWNER TO postgres;

--
-- Name: forgotten_passwords_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE forgotten_passwords_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE forgotten_passwords_id_seq OWNER TO postgres;

--
-- Name: forgotten_passwords_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE forgotten_passwords_id_seq OWNED BY forgotten_passwords.id;


--
-- Name: invitation_attempts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE invitation_attempts (
    id integer NOT NULL,
    attempt_at timestamp with time zone NOT NULL,
    participant_id integer NOT NULL
);


ALTER TABLE invitation_attempts OWNER TO postgres;

--
-- Name: invitation_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE invitation_attempts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE OR REPLACE FUNCTION is_invitation_harassed(subscription participants.subscription_id%TYPE, email participants.email%TYPE, attempts INTEGER = 5, release INTEGER = 12)
	RETURNS BOOLEAN AS $BODY$
DECLARE harassed BOOLEAN NOT NULL DEFAULT TRUE;
BEGIN
	EXECUTE format(
		$$SELECT EXISTS (
			SELECT 1
			FROM invitation_attempts
			WHERE participant_id = (
	  			SELECT id
		  		FROM participants
		  		WHERE subscription_id = %L
		  		AND email = %L
	  		)
	  		AND attempt_at + INTERVAL '1 HOUR' * %L > NOW()
	  		HAVING COUNT(*) >= %L
	  	)$$,
		subscription,
		email,
		release,
		attempts
	)
	INTO harassed;
	RETURN harassed;
END;
$BODY$
LANGUAGE plpgsql;

ALTER FUNCTION is_invitation_harassed(subscription participants.subscription_id%TYPE, email participants.email%TYPE, attempts INTEGER, release INTEGER) OWNER TO postgres;


ALTER TABLE invitation_attempts_id_seq OWNER TO postgres;

--
-- Name: invitation_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE invitation_attempts_id_seq OWNED BY invitation_attempts.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE notifications (
    id integer NOT NULL,
    subscription_id integer NOT NULL,
    notified_at timestamp with time zone NOT NULL
);


ALTER TABLE notifications OWNER TO postgres;

--
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE notifications_id_seq OWNER TO postgres;

--
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE notifications_id_seq OWNED BY notifications.id;


--
-- Name: page_visits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE page_visits (
    id integer NOT NULL,
    page_url character varying NOT NULL,
    visited_at timestamp with time zone NOT NULL
);


ALTER TABLE page_visits OWNER TO postgres;

--
-- Name: page_visits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE page_visits_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE page_visits_id_seq OWNER TO postgres;

--
-- Name: page_visits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE page_visits_id_seq OWNED BY page_visits.id;


--
-- Name: pages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE pages (
    url character varying NOT NULL,
    content text NOT NULL
);


ALTER TABLE pages OWNER TO postgres;

--
-- Name: part_visits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE part_visits (
    id integer NOT NULL,
    part_id integer NOT NULL,
    visited_at timestamp with time zone NOT NULL
);


ALTER TABLE part_visits OWNER TO postgres;

--
-- Name: part_visits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE part_visits_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE part_visits_id_seq OWNER TO postgres;

--
-- Name: part_visits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE part_visits_id_seq OWNED BY part_visits.id;


--
-- Name: participants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE participants (
    id integer NOT NULL,
    email citext NOT NULL,
    subscription_id integer NOT NULL,
    code character varying(64) NOT NULL,
    invited_at timestamp with time zone NOT NULL,
    accepted boolean NOT NULL,
    decided_at timestamp with time zone
);


ALTER TABLE participants OWNER TO postgres;

--
-- Name: participants_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE participants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE participants_id_seq OWNER TO postgres;

--
-- Name: participants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE participants_id_seq OWNED BY participants.id;


--
-- Name: parts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE parts (
    id integer NOT NULL,
    page_url character varying NOT NULL,
    content text NOT NULL,
    snapshot character varying(40) NOT NULL,
    expression expression NOT NULL
);


ALTER TABLE parts OWNER TO postgres;

--
-- Name: parts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE parts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE parts_id_seq OWNER TO postgres;

--
-- Name: parts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE parts_id_seq OWNED BY parts.id;


--
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE subscriptions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    part_id integer NOT NULL,
    "interval" interval NOT NULL,
    last_update timestamp with time zone NOT NULL,
    snapshot character varying(40) NOT NULL
);


ALTER TABLE subscriptions OWNER TO postgres;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE subscriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE subscriptions_id_seq OWNER TO postgres;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE subscriptions_id_seq OWNED BY subscriptions.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE users (
    id integer NOT NULL,
    email citext NOT NULL,
    password character varying(255) NOT NULL,
    role character varying NOT NULL
);


ALTER TABLE users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- Name: verification_codes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE verification_codes (
    id integer NOT NULL,
    user_id integer NOT NULL,
    code character varying(91) NOT NULL,
    used boolean NOT NULL,
    used_at timestamp with time zone
);


ALTER TABLE verification_codes OWNER TO postgres;

--
-- Name: verification_codes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE verification_codes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE verification_codes_id_seq OWNER TO postgres;

--
-- Name: verification_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE verification_codes_id_seq OWNED BY verification_codes.id;


SET search_path = unit_tests, pg_catalog;

--
-- Name: dependencies; Type: TABLE; Schema: unit_tests; Owner: postgres
--

CREATE TABLE dependencies (
    dependency_id bigint NOT NULL,
    dependent_ns text,
    dependent_function_name text NOT NULL,
    depends_on_ns text,
    depends_on_function_name text NOT NULL
);


ALTER TABLE dependencies OWNER TO postgres;

--
-- Name: dependencies_dependency_id_seq; Type: SEQUENCE; Schema: unit_tests; Owner: postgres
--

CREATE SEQUENCE dependencies_dependency_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dependencies_dependency_id_seq OWNER TO postgres;

--
-- Name: dependencies_dependency_id_seq; Type: SEQUENCE OWNED BY; Schema: unit_tests; Owner: postgres
--

ALTER SEQUENCE dependencies_dependency_id_seq OWNED BY dependencies.dependency_id;


--
-- Name: test_details; Type: TABLE; Schema: unit_tests; Owner: postgres
--

CREATE TABLE test_details (
    id bigint NOT NULL,
    test_id integer NOT NULL,
    function_name text NOT NULL,
    message text NOT NULL,
    ts timestamp without time zone DEFAULT timezone('UTC'::text, now()) NOT NULL,
    status boolean NOT NULL,
    executed boolean NOT NULL
);


ALTER TABLE test_details OWNER TO postgres;

--
-- Name: test_details_id_seq; Type: SEQUENCE; Schema: unit_tests; Owner: postgres
--

CREATE SEQUENCE test_details_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE test_details_id_seq OWNER TO postgres;

--
-- Name: test_details_id_seq; Type: SEQUENCE OWNED BY; Schema: unit_tests; Owner: postgres
--

ALTER SEQUENCE test_details_id_seq OWNED BY test_details.id;


--
-- Name: tests; Type: TABLE; Schema: unit_tests; Owner: postgres
--

CREATE TABLE tests (
    test_id integer NOT NULL,
    started_on timestamp without time zone DEFAULT timezone('UTC'::text, now()) NOT NULL,
    completed_on timestamp without time zone,
    total_tests integer DEFAULT 0,
    failed_tests integer DEFAULT 0,
    skipped_tests integer DEFAULT 0
);


ALTER TABLE tests OWNER TO postgres;

--
-- Name: tests_test_id_seq; Type: SEQUENCE; Schema: unit_tests; Owner: postgres
--

CREATE SEQUENCE tests_test_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE tests_test_id_seq OWNER TO postgres;

--
-- Name: tests_test_id_seq; Type: SEQUENCE OWNED BY; Schema: unit_tests; Owner: postgres
--

ALTER SEQUENCE tests_test_id_seq OWNED BY tests.test_id;


SET search_path = public, pg_catalog;

--
-- Name: forgotten_passwords id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY forgotten_passwords ALTER COLUMN id SET DEFAULT nextval('forgotten_passwords_id_seq'::regclass);


--
-- Name: invitation_attempts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY invitation_attempts ALTER COLUMN id SET DEFAULT nextval('invitation_attempts_id_seq'::regclass);


--
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY notifications ALTER COLUMN id SET DEFAULT nextval('notifications_id_seq'::regclass);


--
-- Name: page_visits id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY page_visits ALTER COLUMN id SET DEFAULT nextval('page_visits_id_seq'::regclass);


--
-- Name: part_visits id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY part_visits ALTER COLUMN id SET DEFAULT nextval('part_visits_id_seq'::regclass);


--
-- Name: participants id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY participants ALTER COLUMN id SET DEFAULT nextval('participants_id_seq'::regclass);


--
-- Name: parts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY parts ALTER COLUMN id SET DEFAULT nextval('parts_id_seq'::regclass);


--
-- Name: subscriptions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscriptions ALTER COLUMN id SET DEFAULT nextval('subscriptions_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- Name: verification_codes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY verification_codes ALTER COLUMN id SET DEFAULT nextval('verification_codes_id_seq'::regclass);


SET search_path = unit_tests, pg_catalog;

--
-- Name: dependencies dependency_id; Type: DEFAULT; Schema: unit_tests; Owner: postgres
--

ALTER TABLE ONLY dependencies ALTER COLUMN dependency_id SET DEFAULT nextval('dependencies_dependency_id_seq'::regclass);


--
-- Name: test_details id; Type: DEFAULT; Schema: unit_tests; Owner: postgres
--

ALTER TABLE ONLY test_details ALTER COLUMN id SET DEFAULT nextval('test_details_id_seq'::regclass);


--
-- Name: tests test_id; Type: DEFAULT; Schema: unit_tests; Owner: postgres
--

ALTER TABLE ONLY tests ALTER COLUMN test_id SET DEFAULT nextval('tests_test_id_seq'::regclass);


SET search_path = public, pg_catalog;

--
-- Name: forgotten_passwords forgotten_passwords_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY forgotten_passwords
    ADD CONSTRAINT forgotten_passwords_id PRIMARY KEY (id);


--
-- Name: invitation_attempts invitation_attempts_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY invitation_attempts
    ADD CONSTRAINT invitation_attempts_id PRIMARY KEY (id);


--
-- Name: participants invitations_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY participants
    ADD CONSTRAINT invitations_id PRIMARY KEY (id);


--
-- Name: notifications notifications_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY notifications
    ADD CONSTRAINT notifications_id PRIMARY KEY (id);


--
-- Name: page_visits page_visits_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY page_visits
    ADD CONSTRAINT "page_visits_ID" PRIMARY KEY (id);


--
-- Name: pages pages_url; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY pages
    ADD CONSTRAINT pages_url PRIMARY KEY (url);


--
-- Name: part_visits part_visits_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY part_visits
    ADD CONSTRAINT "part_visits_ID" PRIMARY KEY (id);


--
-- Name: participants participants_email_subscription_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY participants
    ADD CONSTRAINT participants_email_subscription_id UNIQUE (email, subscription_id);


--
-- Name: parts parts_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY parts
    ADD CONSTRAINT "parts_ID" PRIMARY KEY (id);


--
-- Name: parts parts_page_url_expression; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY parts
    ADD CONSTRAINT parts_page_url_expression UNIQUE (page_url, expression);


--
-- Name: subscriptions subscribed_parts_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscriptions
    ADD CONSTRAINT "subscribed_parts_ID" PRIMARY KEY (id);


--
-- Name: subscriptions subscribed_parts_subscriber_id_part_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscriptions
    ADD CONSTRAINT subscribed_parts_subscriber_id_part_id UNIQUE (user_id, part_id);


--
-- Name: users users_email; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_email UNIQUE (email);


--
-- Name: users users_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_id PRIMARY KEY (id);


--
-- Name: verification_codes verification_codes_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY verification_codes
    ADD CONSTRAINT verification_codes_id PRIMARY KEY (id);


--
-- Name: verification_codes verification_codes_user_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY verification_codes
    ADD CONSTRAINT verification_codes_user_id UNIQUE (user_id);


SET search_path = unit_tests, pg_catalog;

--
-- Name: dependencies dependencies_pkey; Type: CONSTRAINT; Schema: unit_tests; Owner: postgres
--

ALTER TABLE ONLY dependencies
    ADD CONSTRAINT dependencies_pkey PRIMARY KEY (dependency_id);


--
-- Name: test_details test_details_pkey; Type: CONSTRAINT; Schema: unit_tests; Owner: postgres
--

ALTER TABLE ONLY test_details
    ADD CONSTRAINT test_details_pkey PRIMARY KEY (id);


--
-- Name: tests tests_pkey; Type: CONSTRAINT; Schema: unit_tests; Owner: postgres
--

ALTER TABLE ONLY tests
    ADD CONSTRAINT tests_pkey PRIMARY KEY (test_id);


--
-- Name: unit_tests_dependencies_dependency_id_inx; Type: INDEX; Schema: unit_tests; Owner: postgres
--

CREATE INDEX unit_tests_dependencies_dependency_id_inx ON dependencies USING btree (dependency_id);


--
-- Name: unit_tests_test_details_status_inx; Type: INDEX; Schema: unit_tests; Owner: postgres
--

CREATE INDEX unit_tests_test_details_status_inx ON test_details USING btree (status);


--
-- Name: unit_tests_test_details_test_id_inx; Type: INDEX; Schema: unit_tests; Owner: postgres
--

CREATE INDEX unit_tests_test_details_test_id_inx ON test_details USING btree (test_id);


--
-- Name: unit_tests_tests_completed_on_inx; Type: INDEX; Schema: unit_tests; Owner: postgres
--

CREATE INDEX unit_tests_tests_completed_on_inx ON tests USING btree (completed_on);


--
-- Name: unit_tests_tests_failed_tests_inx; Type: INDEX; Schema: unit_tests; Owner: postgres
--

CREATE INDEX unit_tests_tests_failed_tests_inx ON tests USING btree (failed_tests);


--
-- Name: unit_tests_tests_started_on_inx; Type: INDEX; Schema: unit_tests; Owner: postgres
--

CREATE INDEX unit_tests_tests_started_on_inx ON tests USING btree (started_on);


SET search_path = public, pg_catalog;

--
-- Name: pages pages_aiu; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER pages_aiu AFTER INSERT OR UPDATE ON pages FOR EACH ROW EXECUTE PROCEDURE record_page_access();


--
-- Name: participants participants_ai; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER participants_ai AFTER INSERT ON participants FOR EACH ROW EXECUTE PROCEDURE record_invitation();


--
-- Name: participants participants_au; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER participants_au AFTER UPDATE ON participants FOR EACH ROW EXECUTE PROCEDURE record_invitation();


--
-- Name: parts parts_aiu; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER parts_aiu AFTER INSERT OR UPDATE ON parts FOR EACH ROW EXECUTE PROCEDURE record_part_access();


--
-- Name: subscriptions subscriptions_au; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER subscriptions_au AFTER UPDATE ON subscriptions FOR EACH ROW EXECUTE PROCEDURE notify_subscriptions();


SET search_path = unit_tests, pg_catalog;

--
-- Name: test_details test_details_test_id_fkey; Type: FK CONSTRAINT; Schema: unit_tests; Owner: postgres
--

ALTER TABLE ONLY test_details
    ADD CONSTRAINT test_details_test_id_fkey FOREIGN KEY (test_id) REFERENCES tests(test_id);


--
-- PostgreSQL database dump complete
--

