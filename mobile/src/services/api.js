/**
 * API service for communicating with the Laravel backend.
 *
 * All methods return parsed JSON or throw errors.
 * Token is stored in memory (would use SecureStore in production).
 */

const getBaseUrl = () => {
  if (typeof window !== 'undefined' && window.location && window.location.hostname) {
    return `http://${window.location.hostname}:8000/api`;
  }
  return 'http://localhost:8000/api';
};

const BASE_URL = getBaseUrl();

let authToken = null;

export const setAuthToken = (token) => {
  authToken = token;
};

const headers = () => ({
  'Content-Type': 'application/json',
  Accept: 'application/json',
  ...(authToken ? { Authorization: `Bearer ${authToken}` } : {}),
});

const handleResponse = async (response) => {
  const data = await response.json();

  if (!response.ok) {
    const error = new Error(data.message || 'Request failed');
    error.status = response.status;
    error.errors = data.errors || {};
    throw error;
  }

  return data;
};

// ── Auth ────────────────────────────────────────────────────

export const login = async (email, password) => {
  const response = await fetch(`${BASE_URL}/login`, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ email, password }),
  });
  const data = await handleResponse(response);
  setAuthToken(data.data.token);
  return data;
};

const ensureAuthenticated = async () => {
  if (!authToken) {
    try {
      await login('demo@guisedup.com', 'password');
    } catch (e) {
      console.warn('Auto-login failed:', e);
    }
  }
};

export const register = async (name, email, password, passwordConfirmation) => {
  const response = await fetch(`${BASE_URL}/register`, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    }),
  });
  const data = await handleResponse(response);
  setAuthToken(data.data.token);
  return data;
};

// ── Feed ────────────────────────────────────────────────────

export const getFeed = async (page = 1, perPage = 20) => {
  await ensureAuthenticated();
  const response = await fetch(
    `${BASE_URL}/feed?page=${page}&per_page=${perPage}`,
    { headers: headers() },
  );
  return handleResponse(response);
};

// ── Search ──────────────────────────────────────────────────

export const searchPosts = async (query, page = 1, perPage = 20) => {
  await ensureAuthenticated();
  const encoded = encodeURIComponent(query);
  const response = await fetch(
    `${BASE_URL}/search?q=${encoded}&page=${page}&per_page=${perPage}`,
    { headers: headers() },
  );
  return handleResponse(response);
};

// ── Posts ────────────────────────────────────────────────────

export const createPost = async (content) => {
  await ensureAuthenticated();
  const response = await fetch(`${BASE_URL}/posts`, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ content }),
  });
  return handleResponse(response);
};

// ── Interactions ────────────────────────────────────────────

export const toggleReaction = async (postId, type) => {
  await ensureAuthenticated();
  const response = await fetch(`${BASE_URL}/interactions`, {
    method: 'POST',
    headers: headers(),
    body: JSON.stringify({ post_id: postId, type }),
  });
  return handleResponse(response);
};
