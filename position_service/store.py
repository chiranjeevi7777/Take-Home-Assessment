import threading
from typing import Dict, Set, Tuple


class PositionStore:
    """
    Thread-safe in-memory store for maintaining net position by trading symbol
    and guaranteeing event idempotency using event_id tracking.
    """
    def __init__(self):
        self._lock = threading.Lock()
        self._positions: Dict[str, int] = {}
        self._seen_event_ids: Set[str] = set()

    def process_event(self, event_id: str, symbol: str, transaction_type: str, quantity: int) -> Tuple[bool, bool]:
        """
        Atomically processes an order event.

        Args:
            event_id: Unique string identifier for the event.
            symbol: Trading symbol (case preserved).
            transaction_type: Exactly 'BUY' or 'SELL'.
            quantity: Positive integer quantity.

        Returns:
            (processed, is_duplicate)
            If event_id was previously seen, returns (False, True).
            Otherwise applies position delta and returns (True, False).
        """
        with self._lock:
            if event_id in self._seen_event_ids:
                return False, True

            self._seen_event_ids.add(event_id)

            current_pos = self._positions.get(symbol, 0)
            if transaction_type == "BUY":
                self._positions[symbol] = current_pos + quantity
            elif transaction_type == "SELL":
                self._positions[symbol] = current_pos - quantity
            else:
                raise ValueError(f"Invalid transaction_type: '{transaction_type}'")

            return True, False

    def get_positions(self) -> Dict[str, int]:
        """
        Returns a snapshot copy of current net positions for all tracked symbols.
        Includes zero and negative positions.
        """
        with self._lock:
            return dict(self._positions)

    def is_event_seen(self, event_id: str) -> bool:
        """
        Checks if an event_id has already been processed.
        """
        with self._lock:
            return event_id in self._seen_event_ids

    def clear(self) -> None:
        """
        Clears all stored positions and event IDs (useful for reset between tests).
        """
        with self._lock:
            self._positions.clear()
            self._seen_event_ids.clear()
