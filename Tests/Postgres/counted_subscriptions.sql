CREATE OR REPLACE FUNCTION unit_tests.counted_subscriptions()
	RETURNS TEST_RESULT AS $$
DECLARE
	message TEST_RESULT;
	count INTEGER;
	expected_count CONSTANT INTEGER := 3;
BEGIN
	INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
		(2, 'bar.cz', ROW ('//bar', 'xpath'), 'bar', 'barSnap'), (3, 'baz.cz', ROW ('//baz', 'xpath'), 'baz', 'bazSnap');

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
$$
LANGUAGE plpgsql;