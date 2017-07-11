CREATE OR REPLACE FUNCTION public.counted_subscriptions(OUT part_id INTEGER, OUT occurrences BIGINT) RETURNS SETOF RECORD AS $$
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
$$ LANGUAGE SQL;