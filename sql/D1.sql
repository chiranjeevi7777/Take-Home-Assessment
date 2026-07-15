-- D1: Calculate User Authenticity Score & Interaction Metrics
-- Demonstrates: Multi-table Joins, Aggregation (COUNT, AVG), Group By, and NULL handling (COALESCE)
--
-- Objective: Compute for each user their profile details, the number of posts they have created,
-- the average authenticity score of their posts, and the total reactions (interactions)
-- their posts have received from other users.

SELECT
    u.id AS user_id,
    u.name AS user_name,
    u.email AS user_email,
    u.authenticity_score AS user_profile_authenticity,
    COUNT(DISTINCT p.id) AS total_posts,
    COALESCE(ROUND(AVG(p.authenticity_score), 2), 0.00) AS avg_post_authenticity,
    COUNT(i.id) AS total_reactions_received
FROM
    users u
LEFT JOIN
    posts p ON p.user_id = u.id
LEFT JOIN
    interactions i ON i.post_id = p.id
GROUP BY
    u.id,
    u.name,
    u.email,
    u.authenticity_score
ORDER BY
    total_reactions_received DESC,
    avg_post_authenticity DESC;
