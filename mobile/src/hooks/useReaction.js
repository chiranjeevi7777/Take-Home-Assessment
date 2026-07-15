import { useState, useCallback } from 'react';
import { toggleReaction } from '../services/api';

/**
 * Custom hook for toggling reactions with optimistic updates.
 */
export const useReaction = (updatePost) => {
  const [pending, setPending] = useState({});

  const react = useCallback(
    async (postId, type, currentReaction) => {
      // Prevent double-tap
      if (pending[postId]) return;

      setPending((prev) => ({ ...prev, [postId]: true }));

      // Optimistic update
      const isRemoving = currentReaction === type;
      updatePost(postId, {
        user_reaction: isRemoving ? null : type,
        reactions_count: isRemoving ? -1 : 1, // Relative update handled in component
      });

      try {
        await toggleReaction(postId, type);
      } catch {
        // Revert optimistic update on failure
        updatePost(postId, {
          user_reaction: currentReaction,
        });
      } finally {
        setPending((prev) => ({ ...prev, [postId]: false }));
      }
    },
    [pending, updatePost],
  );

  return { react, pending };
};
