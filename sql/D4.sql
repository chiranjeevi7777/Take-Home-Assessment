-- D4: Ranked Top 3 Recent Authentic Posts Per User
-- Demonstrates: Common Table Expressions (CTEs), Window Functions (ROW_NUMBER() / DENSE_RANK() with PARTITION BY)
--
-- Objective: For each user, retrieve their top 3 most authentic posts created within the last 7 days.
-- The query ranks posts per user using a window function partitioned by the user_id, ordered by authenticity_score.
-- It filters out older posts and ranks only the top 3 per user.

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
