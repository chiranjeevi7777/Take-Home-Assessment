import React from 'react';
import { StatusBar } from 'react-native';
import { registerRootComponent } from 'expo';
import FeedScreen from './src/screens/FeedScreen';
import { colors } from './src/styles/theme';

/**
 * Guised Up — Mobile App Entry Point
 *
 * In a full app, this would include navigation (React Navigation),
 * auth context, and theme provider. For the assessment, we render
 * the Feed Screen directly.
 */
function App() {
  return (
    <>
      <StatusBar
        barStyle="dark-content"
        backgroundColor={colors.background}
      />
      <FeedScreen />
    </>
  );
}

registerRootComponent(App);

