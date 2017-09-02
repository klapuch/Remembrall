--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.4
-- Dumped by pg_dump version 9.6.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

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
-- Name: is_invitation_harassed(integer, citext, integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION is_invitation_harassed(subscription integer, email citext, attempts integer DEFAULT 5, release integer DEFAULT 12) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
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
$_$;


ALTER FUNCTION public.is_invitation_harassed(subscription integer, email citext, attempts integer, release integer) OWNER TO postgres;

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
-- Name: to_iso8601(timestamp with time zone); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION to_iso8601(timestamp with time zone) RETURNS text
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $_$
BEGIN
	RETURN to_char ($1, 'YYYY-MM-DD"T"HH24:MI:SS"Z"');
END
$_$;


ALTER FUNCTION public.to_iso8601(timestamp with time zone) OWNER TO postgres;

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


--
-- PostgreSQL database dump complete
--

