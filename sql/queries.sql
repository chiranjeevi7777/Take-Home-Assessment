-- ==========================================
-- Guised Up — Database Analytical Queries
-- ==========================================

-- ------------------------------------------
-- D1: Calculate User Authenticity Score & Interaction Metrics
-- Demonstrates: Multi-table Joins, Aggregation (COUNT, AVG), Group By, and NULL handling (COALESCE)
-- Objective: Compute for each user their profile details, the number of posts they have created,
-- the average authenticity score of their posts, and the total reactions (interactions)
-- their posts have received from other users.
-- ------------------------------------------

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


-- ------------------------------------------
-- D2: Top Influential Mutual Relationships (Self-Join and Bidirectional Metrics)
-- Demonstrates: Self-Join (joining a table to itself), Composite Key Joins, and Bidirectional Aggregation
-- Objective: Find pairs of users who mutually follow each other (User A follows User B AND User B follows User A).
-- Calculate the combined bidirectional relationship strength (strength of A->B + strength of B->A)
-- to identify the strongest interpersonal social bonds on the platform.
-- ------------------------------------------

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


-- ------------------------------------------
-- D3: Highly Authentic Active Creators
-- Demonstrates: Subqueries (scalar and tabular), Aggregation Filters (HAVING with inner queries), and Join logic
-- Objective: Discover users who are active (have posted at least 3 times) and whose average post-level
-- authenticity score is higher than the overall platform average post-level authenticity score.
-- This helps identify the platform's leading "authentic creators" for seeding user interest vectors.
-- ------------------------------------------

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


-- ------------------------------------------
-- D4: Ranked Top 3 Recent Authentic Posts Per User
-- Demonstrates: Common Table Expressions (CTEs), Window Functions (ROW_NUMBER() / DENSE_RANK() with PARTITION BY)
-- Objective: For each user, retrieve their top 3 most authentic posts created within the last 7 days.
-- The query ranks posts per user using a window function partitioned by the user_id, ordered by authenticity_score.
-- It filters out older posts and ranks only the top 3 per user.
-- ------------------------------------------

WITH RankedRecentPosts AS (
    SELECT
        p.id AS post_id,
        p.user_id,
        u.name AS author_name,
        p.content AS post_content,
        p.authenticity_score AS post_authenticity,
        p.created_at AS post_created_at,
        ROW_NUMBER() OVER (
            PARTITION BY p.user_id 
            ORDER BY p.authenticity_score DESC, p.created_at DESC
        ) AS authenticity_rank
    FROM
        posts p
    INNER JOIN
        users u ON u.id = p.user_id
    WHERE
        -- Filter for posts created in the last 7 days
        -- Syntax handles standard databases (MySQL/PostgreSQL)
        p.created_at >= NOW() - INTERVAL 7 DAY
)
SELECT
    user_id,
    author_name,
    post_id,
    post_content,
    post_authenticity,
    post_created_at,
    authenticity_rank
FROM
    RankedRecentPosts
WHERE
    authenticity_rank <= 3
ORDER BY
    user_id ASC,
    authenticity_rank ASC;
