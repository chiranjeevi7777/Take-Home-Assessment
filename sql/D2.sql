-- D2: Top Influential Mutual Relationships (Self-Join and Bidirectional Metrics)
-- Demonstrates: Self-Join (joining a table to itself), Composite Key Joins, and Bidirectional Aggregation
--
-- Objective: Find pairs of users who mutually follow each other (User A follows User B AND User B follows User A).
-- Calculate the combined bidirectional relationship strength (strength of A->B + strength of B->A)
-- to identify the strongest interpersonal social bonds on the platform.

SELECT
    LEAST(r1.follower_id, r1.following_id) AS user_a_id,
    GREATEST(r1.follower_id, r1.following_id) AS user_b_id,
    u1.name AS user_a_name,
    u2.name AS user_b_name,
    r1.strength AS strength_a_to_b,
    r2.strength AS strength_b_to_a,
    (r1.strength + r2.strength) AS combined_relationship_strength
FROM
    relationships r1
INNER JOIN
    relationships r2 ON r1.follower_id = r2.following_id 
                    AND r1.following_id = r2.follower_id
INNER JOIN
    users u1 ON u1.id = r1.follower_id
INNER JOIN
    users u2 ON u2.id = r1.following_id
WHERE
    -- Prevent duplicate rows for the same pair (e.g. A-B and B-A) by enforcing id ordering
    r1.follower_id < r1.following_id
ORDER BY
    combined_relationship_strength DESC;
