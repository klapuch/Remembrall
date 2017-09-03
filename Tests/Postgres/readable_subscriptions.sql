CREATE OR REPLACE FUNCTION unit_tests.intervals_in_iso8601() RETURNS TEST_RESULT AS $$
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
	(1, 3, '00:00:20'::interval, NOW(), md5(random()::TEXT)),
	(1, 5, 'PT0S', NOW(), md5(random()::TEXT)),
	(2, 6, 'PT555S', NOW(), md5(random()::TEXT)),
	(2, 7, 'PT2M3S', NOW(), md5(random()::TEXT));

	SELECT array_agg(interval_seconds), array_agg(interval)
	FROM readable_subscriptions()
	INTO second_intervals, iso_intervals;
	IF second_intervals IS NOT DISTINCT FROM expected_second_intervals
	THEN
		SELECT assert.ok('Second intervals are matching.')
		INTO message;
	ELSE
		SELECT assert.fail(format('Expected intervals in seconds were %s, actual %s', expected_second_intervals, second_intervals))
		INTO message;
	END IF;
	IF iso_intervals IS NOT DISTINCT FROM expected_iso_intervals
	THEN
		SELECT assert.ok('ISO intervals are matching.')
		INTO message;
	ELSE
		SELECT assert.fail(format('Expected ISO intervals were %s, actual %s', expected_iso_intervals, iso_intervals))
		INTO message;
	END IF;
	RETURN message;
END
$$
LANGUAGE plpgsql;