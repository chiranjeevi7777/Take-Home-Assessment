import React from 'react';
import { View, StyleSheet, Animated } from 'react-native';
import { colors, spacing, borderRadius, shadows } from '../styles/theme';

/**
 * Skeleton loading placeholder for post cards.
 *
 * Displays a shimmer-like static placeholder while content loads.
 * Shows 3 skeleton cards by default.
 */
const LoadingSkeleton = ({ count = 3 }) => {
  return (
    <View style={styles.wrapper}>
      {Array.from({ length: count }).map((_, i) => (
        <View key={i} style={styles.card}>
          {/* Header skeleton */}
          <View style={styles.header}>
            <View style={styles.avatarSkel} />
            <View style={styles.headerLines}>
              <View style={[styles.line, { width: '40%' }]} />
              <View style={[styles.lineSmall, { width: '25%' }]} />
            </View>
            <View style={styles.badgeSkel} />
          </View>

          {/* Content skeleton */}
          <View style={[styles.line, { width: '100%' }]} />
          <View style={[styles.line, { width: '85%' }]} />
          <View style={[styles.line, { width: '65%' }]} />

          {/* Footer skeleton */}
          <View style={styles.footer}>
            <View style={[styles.lineSmall, { width: '20%' }]} />
            <View style={[styles.lineSmall, { width: '15%' }]} />
          </View>
        </View>
      ))}
    </View>
  );
};

const styles = StyleSheet.create({
  wrapper: {
    paddingTop: spacing.sm,
  },
  card: {
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
    marginBottom: spacing.lg,
  },
  avatarSkel: {
    width: 40,
    height: 40,
    borderRadius: borderRadius.full,
    backgroundColor: colors.divider,
  },
  headerLines: {
    flex: 1,
    marginLeft: spacing.md,
  },
  badgeSkel: {
    width: 44,
    height: 24,
    borderRadius: borderRadius.full,
    backgroundColor: colors.divider,
  },
  line: {
    height: 14,
    borderRadius: 7,
    backgroundColor: colors.divider,
    marginBottom: spacing.sm,
  },
  lineSmall: {
    height: 10,
    borderRadius: 5,
    backgroundColor: colors.divider,
    marginBottom: spacing.xs,
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: spacing.md,
    paddingTop: spacing.md,
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: colors.divider,
  },
});

export default LoadingSkeleton;
