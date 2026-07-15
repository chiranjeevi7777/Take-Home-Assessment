import { useState, useCallback, useRef } from 'react';
import { getFeed } from '../services/api';

/**
 * Custom hook for infinite-scroll feed with pagination.
 *
 * Returns feed data, loading states, and control functions.
 * Handles page tracking, deduplication, and end-of-list detection.
 */
export const useFeed = () => {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState(null);
  const [hasMore, setHasMore] = useState(true);

  const pageRef = useRef(1);
  const loadingRef = useRef(false);

  const loadFeed = useCallback(async (page = 1, isRefresh = false) => {
    if (loadingRef.current) return;
    loadingRef.current = true;

    if (isRefresh) {
      setRefreshing(true);
    } else {
      setLoading(true);
    }
    setError(null);

    try {
      const response = await getFeed(page);
      const newPosts = response.data;
      const meta = response.meta;

      if (isRefresh || page === 1) {
        setPosts(newPosts);
      } else {
        setPosts((prev) => {
          // Deduplicate by id
          const existingIds = new Set(prev.map((p) => p.id));
          const unique = newPosts.filter((p) => !existingIds.has(p.id));
          return [...prev, ...unique];
        });
      }

      setHasMore(meta.current_page < meta.last_page);
      pageRef.current = meta.current_page;
    } catch (err) {
      setError(err.message || 'Failed to load feed');
    } finally {
      setLoading(false);
      setRefreshing(false);
      loadingRef.current = false;
    }
  }, []);

  const loadMore = useCallback(() => {
    if (!hasMore || loadingRef.current) return;
    loadFeed(pageRef.current + 1);
  }, [hasMore, loadFeed]);

  const refresh = useCallback(() => {
    pageRef.current = 1;
    setHasMore(true);
    loadFeed(1, true);
  }, [loadFeed]);

  const updatePost = useCallback((postId, updates) => {
    setPosts((prev) =>
      prev.map((p) => (p.id === postId ? { ...p, ...updates } : p)),
    );
  }, []);

  return {
    posts,
    loading,
    refreshing,
    error,
    hasMore,
    loadFeed,
    loadMore,
    refresh,
    updatePost,
  };
};
