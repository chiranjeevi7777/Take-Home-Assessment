import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, spacing, typography, borderRadius, shadows } from '../styles/theme';
import ReactionButton from './ReactionButton';

/**
 * Post card component for the feed.
 *
 * Displays author info, authenticity badge, content,
 * reaction count, and time since posted.
 */
const PostCard = ({ post, onReaction, reactionPending }) => {
  const authColor = getAuthColor(post.user.authenticity_score);
  const timeAgo = formatTimeAgo(post.created_at);

  return (
    <View style={styles.container}>
      {/* Header: Author + Authenticity */}
      <View style={styles.header}>
        <View style={styles.avatar}>
          <Text style={styles.avatarText}>
            {post.user.name.charAt(0).toUpperCase()}
          </Text>
        </View>

        <View style={styles.headerInfo}>
          <Text style={styles.authorName}>{post.user.name}</Text>
          <Text style={styles.timeAgo}>{timeAgo}</Text>
        </View>

        <View style={[styles.authBadge, { backgroundColor: authColor + '18' }]}>
          <View style={[styles.authDot, { backgroundColor: authColor }]} />
          <Text style={[styles.authScore, { color: authColor }]}>
            {Math.round(post.user.authenticity_score)}
          </Text>
        </View>
      </View>

      {/* Content */}
      <Text style={styles.content}>{post.content}</Text>

      {/* Footer: Reactions + Score */}
      <View style={styles.footer}>
        <ReactionButton
          postId={post.id}
          currentReaction={post.user_reaction}
          reactionsCount={post.reactions_count}
          onReaction={onReaction}
          disabled={reactionPending}
        />

        {post.ranking_score != null && (
          <Text style={styles.rankingScore}>
            {(post.ranking_score * 100).toFixed(0)}% relevant
          </Text>
        )}
      </View>
    </View>
  );
};

const getAuthColor = (score) => {
  if (score >= 75) return colors.highAuth;
  if (score >= 50) return colors.medAuth;
  return colors.lowAuth;
};

const formatTimeAgo = (isoString) => {
  const now = new Date();
  const date = new Date(isoString);
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);

  if (diffMins < 1) return 'just now';
  if (diffMins < 60) return `${diffMins}m`;
  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return `${diffHours}h`;
  const diffDays = Math.floor(diffHours / 24);
  if (diffDays < 7) return `${diffDays}d`;
  return date.toLocaleDateString();
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: colors.surface,
    marginHorizontal: spacing.lg,
    marginVertical: spacing.sm,
    borderRadius: borderRadius.lg,
    padding: spacing.lg,
    ...shadows.card,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  avatar: {
    width: 40,
    height: 40,
    borderRadius: borderRadius.full,
    backgroundColor: colors.primaryLight,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    ...typography.label,
    color: colors.textInverse,
    fontSize: 16,
  },
  headerInfo: {
    flex: 1,
    marginLeft: spacing.md,
  },
  authorName: {
    ...typography.label,
    color: colors.textPrimary,
  },
  timeAgo: {
    ...typography.caption,
    color: colors.textTertiary,
    marginTop: 2,
  },
  authBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: borderRadius.full,
  },
  authDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    marginRight: spacing.xs,
  },
  authScore: {
    ...typography.caption,
    fontWeight: '700',
  },
  content: {
    ...typography.body,
    color: colors.textPrimary,
    marginBottom: spacing.md,
  },
  footer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: colors.divider,
    paddingTop: spacing.md,
  },
  rankingScore: {
    ...typography.caption,
    color: colors.textTertiary,
  },
});

export default React.memo(PostCard);
