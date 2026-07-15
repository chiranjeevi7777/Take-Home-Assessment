import React from 'react';
import { TouchableOpacity, Text, View, StyleSheet } from 'react-native';
import { colors, spacing, typography, borderRadius } from '../styles/theme';

/**
 * Reaction button with toggle behavior and count display.
 *
 * Shows emoji for reaction type with active state styling.
 */

const REACTION_CONFIG = {
  like: { emoji: '👍', label: 'Like', color: colors.reactionLike },
  love: { emoji: '❤️', label: 'Love', color: colors.reactionLove },
  insightful: { emoji: '💡', label: 'Insightful', color: colors.reactionInsightful },
  disagree: { emoji: '🤔', label: 'Disagree', color: colors.reactionDisagree },
};

const DEFAULT_TYPE = 'like';

const ReactionButton = ({
  postId,
  currentReaction,
  reactionsCount,
  onReaction,
  disabled,
}) => {
  const config = REACTION_CONFIG[currentReaction] || REACTION_CONFIG[DEFAULT_TYPE];
  const isActive = currentReaction != null;

  const handlePress = () => {
    if (disabled) return;
    onReaction(postId, currentReaction || DEFAULT_TYPE, currentReaction);
  };

  const handleLongPress = () => {
    // Future: show reaction picker
  };

  return (
    <View style={styles.container}>
      <TouchableOpacity
        style={[
          styles.button,
          isActive && { backgroundColor: config.color + '15' },
        ]}
        onPress={handlePress}
        onLongPress={handleLongPress}
        activeOpacity={0.7}
        disabled={disabled}
      >
        <Text style={styles.emoji}>{config.emoji}</Text>
        <Text
          style={[
            styles.label,
            isActive && { color: config.color, fontWeight: '600' },
          ]}
        >
          {isActive ? config.label : 'React'}
        </Text>
      </TouchableOpacity>

      {reactionsCount > 0 && (
        <Text style={styles.count}>{reactionsCount}</Text>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: borderRadius.full,
  },
  emoji: {
    fontSize: 16,
    marginRight: spacing.xs,
  },
  label: {
    ...typography.bodySmall,
    color: colors.textSecondary,
  },
  count: {
    ...typography.caption,
    color: colors.textTertiary,
    marginLeft: spacing.sm,
  },
});

export default React.memo(ReactionButton);
