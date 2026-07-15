import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, spacing, typography } from '../styles/theme';

/**
 * Empty state shown when the feed has no posts.
 */
const EmptyState = ({ message, subtitle }) => {
  return (
    <View style={styles.container}>
      <Text style={styles.emoji}>🌱</Text>
      <Text style={styles.message}>{message || 'Nothing here yet'}</Text>
      <Text style={styles.subtitle}>
        {subtitle || 'Be the first to share something authentic.'}
      </Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.xxl,
    paddingVertical: 80,
  },
  emoji: {
    fontSize: 48,
    marginBottom: spacing.lg,
  },
  message: {
    ...typography.h2,
    color: colors.textPrimary,
    textAlign: 'center',
    marginBottom: spacing.sm,
  },
  subtitle: {
    ...typography.body,
    color: colors.textSecondary,
    textAlign: 'center',
    lineHeight: 22,
  },
});

export default EmptyState;
