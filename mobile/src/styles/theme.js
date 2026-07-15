/**
 * Design tokens for the Guised Up mobile app.
 *
 * Authenticity-focused design: warm, organic colors.
 * No neon, no harsh gradients — trustworthy and genuine.
 */

export const colors = {
  // Primary palette — warm earth tones
  primary: '#6B4E3D',
  primaryLight: '#8B6F5E',
  primaryDark: '#4A3228',

  // Accent — warm amber
  accent: '#E8A838',
  accentLight: '#F0C060',

  // Authenticity indicators
  highAuth: '#4A9B6E',
  medAuth: '#D4A843',
  lowAuth: '#C4634F',

  // Backgrounds
  background: '#FAFAF7',
  surface: '#FFFFFF',
  surfaceElevated: '#FFFFFF',

  // Text
  textPrimary: '#1A1A18',
  textSecondary: '#6B6B66',
  textTertiary: '#9E9E99',
  textInverse: '#FFFFFF',

  // Borders & dividers
  border: '#E8E8E3',
  divider: '#F0F0EB',

  // States
  error: '#C4634F',
  errorLight: '#FCEAE6',
  success: '#4A9B6E',
  successLight: '#E6F5EC',

  // Reactions
  reactionLike: '#E8A838',
  reactionLove: '#C4634F',
  reactionInsightful: '#4A9B6E',
  reactionDisagree: '#8B8B86',
};

export const spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  xxl: 32,
};

export const typography = {
  h1: { fontSize: 24, fontWeight: '700', lineHeight: 32 },
  h2: { fontSize: 20, fontWeight: '600', lineHeight: 28 },
  body: { fontSize: 15, fontWeight: '400', lineHeight: 22 },
  bodySmall: { fontSize: 13, fontWeight: '400', lineHeight: 18 },
  caption: { fontSize: 11, fontWeight: '500', lineHeight: 16 },
  label: { fontSize: 13, fontWeight: '600', lineHeight: 18 },
};

export const borderRadius = {
  sm: 8,
  md: 12,
  lg: 16,
  full: 999,
};

export const shadows = {
  card: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.06,
    shadowRadius: 4,
    elevation: 2,
  },
  elevated: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 12,
    elevation: 4,
  },
};
