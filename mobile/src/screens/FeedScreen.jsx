import React, { useEffect, useCallback } from 'react';
import {
  View,
  FlatList,
  ActivityIndicator,
  StyleSheet,
  SafeAreaView,
  Text,
} from 'react-native';

import { useFeed } from '../hooks/useFeed';
import { useSearch } from '../hooks/useSearch';
import { useReaction } from '../hooks/useReaction';

import PostCard from '../components/PostCard';
import SearchBar from '../components/SearchBar';
import LoadingSkeleton from '../components/LoadingSkeleton';
import EmptyState from '../components/EmptyState';
import ErrorState from '../components/ErrorState';

import { colors, spacing, typography } from '../styles/theme';

/**
 * Main feed screen with search, infinite scroll, and reactions.
 *
 * Two modes:
 *   1. Feed mode (default) — ranked, paginated feed
 *   2. Search mode — semantic search results
 */
const FeedScreen = () => {
  const feed = useFeed();
  const search = useSearch();
  const { react, pending } = useReaction(feed.updatePost);

  const isSearching = search.query.length > 0;
  const displayData = isSearching ? search.results : feed.posts;
  const isLoading = isSearching ? search.loading : feed.loading;
  const error = isSearching ? search.error : feed.error;
  const hasMore = isSearching ? search.hasMore : feed.hasMore;

  // Load feed on mount
  useEffect(() => {
    feed.loadFeed(1);
  }, []);

  // ── Handlers ─────────────────────────────────────────────

  const handleReaction = useCallback(
    (postId, type, currentReaction) => {
      react(postId, type, currentReaction);
    },
    [react],
  );

  const handleLoadMore = useCallback(() => {
    if (isSearching) {
      search.loadMore();
    } else {
      feed.loadMore();
    }
  }, [isSearching, search, feed]);

  const handleRefresh = useCallback(() => {
    if (!isSearching) {
      feed.refresh();
    }
  }, [isSearching, feed]);

  const handleSearchChange = useCallback(
    (text) => {
      search.search(text);
    },
    [search],
  );

  const handleClearSearch = useCallback(() => {
    search.clearSearch();
  }, [search]);

  // ── Render Helpers ───────────────────────────────────────

  const renderItem = useCallback(
    ({ item }) => (
      <PostCard
        post={item}
        onReaction={handleReaction}
        reactionPending={pending[item.id]}
      />
    ),
    [handleReaction, pending],
  );

  const renderFooter = () => {
    if (!hasMore) return null;
    return (
      <View style={styles.footer}>
        <ActivityIndicator size="small" color={colors.primary} />
      </View>
    );
  };

  const renderEmpty = () => {
    if (isLoading) return null;

    if (error) {
      return <ErrorState message={error} onRetry={() => feed.loadFeed(1)} />;
    }

    if (isSearching) {
      return (
        <EmptyState
          message="No results found"
          subtitle={`No posts match "${search.query}". Try different keywords.`}
        />
      );
    }

    return <EmptyState />;
  };

  const keyExtractor = useCallback((item) => String(item.id), []);

  // ── Initial Load ─────────────────────────────────────────

  if (feed.loading && feed.posts.length === 0 && !isSearching) {
    return (
      <SafeAreaView style={styles.screen}>
        <View style={styles.headerBar}>
          <Text style={styles.title}>Guised Up</Text>
          <Text style={styles.tagline}>Authenticity First</Text>
        </View>
        <SearchBar
          value={search.query}
          onChangeText={handleSearchChange}
          onClear={handleClearSearch}
        />
        <LoadingSkeleton />
      </SafeAreaView>
    );
  }

  // ── Main Render ──────────────────────────────────────────

  return (
    <SafeAreaView style={styles.screen}>
      <View style={styles.headerBar}>
        <Text style={styles.title}>Guised Up</Text>
        <Text style={styles.tagline}>Authenticity First</Text>
      </View>

      <SearchBar
        value={search.query}
        onChangeText={handleSearchChange}
        onClear={handleClearSearch}
      />

      {isSearching && search.loading && displayData.length === 0 ? (
        <LoadingSkeleton count={2} />
      ) : (
        <FlatList
          data={displayData}
          renderItem={renderItem}
          keyExtractor={keyExtractor}
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.3}
          refreshing={feed.refreshing}
          onRefresh={handleRefresh}
          ListEmptyComponent={renderEmpty}
          ListFooterComponent={renderFooter}
          contentContainerStyle={
            displayData.length === 0 ? styles.emptyContainer : styles.listContent
          }
          showsVerticalScrollIndicator={false}
          removeClippedSubviews={true}
          maxToRenderPerBatch={10}
          windowSize={5}
        />
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: colors.background,
  },
  headerBar: {
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.lg,
    paddingBottom: spacing.sm,
  },
  title: {
    ...typography.h1,
    color: colors.primary,
  },
  tagline: {
    ...typography.caption,
    color: colors.textTertiary,
    letterSpacing: 1,
    textTransform: 'uppercase',
    marginTop: 2,
  },
  listContent: {
    paddingBottom: spacing.xxl,
  },
  emptyContainer: {
    flexGrow: 1,
  },
  footer: {
    paddingVertical: spacing.xl,
    alignItems: 'center',
  },
});

export default FeedScreen;
