CREATE OR REPLACE FUNCTION unit_tests.readable_subscriptions_view() RETURNS TEST_RESULT AS $$
DECLARE
	message TEST_RESULT;
	intervals INTEGER[];
	expected_intervals CONSTANT INTEGER[] := ARRAY[1, 20, 0, 555];
BEGIN
	TRUNCATE subscriptions;
	INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
	(1, 2, 'PT1S', NOW(), md5(random()::TEXT)),
	(1, 3, 'PT20S', NOW(), md5(random()::TEXT)),
	(1, 5, 'PT0S', NOW(), md5(random()::TEXT)),
	(2, 6, 'PT555S', NOW(), md5(random()::TEXT));

	SELECT array_agg(interval_seconds)
	FROM readable_subscriptions
	INTO intervals;
	IF intervals = expected_intervals
	THEN
		SELECT assert.ok('Intervals are matching.')
		INTO message;
	ELSE
		SELECT assert.fail(format('Expected intervals were %s, actual %s', expected_intervals, intervals))
		INTO message;
	END IF;
	RETURN message;
END
$$
LANGUAGE plpgsql;