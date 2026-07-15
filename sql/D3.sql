-- D3: Highly Authentic Active Creators
-- Demonstrates: Subqueries (scalar and tabular), Aggregation Filters (HAVING with inner queries), and Join logic
--
-- Objective: Discover users who are active (have posted at least 3 times) and whose average post-level
-- authenticity score is higher than the overall platform average post-level authenticity score.
-- This helps identify the platform's leading "authentic creators" for seeding user interest vectors.

SELECT
    u.id AS user_id,
    u.name AS creator_name,
    COUNT(p.id) AS posts_count,
    ROUND(AVG(p.authenticity_score), 2) AS creator_avg_authenticity,
    -- Calculate how much higher this user is compared to the platform average
    ROUND(AVG(p.authenticity_score) - (SELECT AVG(authenticity_score) FROM posts), 2) AS authenticity_delta_above_average
FROM
    users u
INNER JOIN
    posts p ON p.user_id = u.id
GROUP BY
    u.id,
    u.name
HAVING
    COUNT(p.id) >= 3
    AND AVG(p.authenticity_score) > (SELECT AVG(authenticity_score) FROM posts)
ORDER BY
    creator_avg_authenticity DESC;
