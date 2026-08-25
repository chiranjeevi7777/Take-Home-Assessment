import threading
import pytest
from position_service.store import PositionStore


def test_buy_and_sell_position_calculations():
    store = PositionStore()
    store.process_event("e1", "RELIANCE", "BUY", 100)
    assert store.get_positions() == {"RELIANCE": 100}

    store.process_event("e2", "RELIANCE", "SELL", 40)
    assert store.get_positions() == {"RELIANCE": 60}


def test_zero_and_negative_positions():
    store = PositionStore()

    # Net zero position
    store.process_event("e1", "INFY", "BUY", 50)
    store.process_event("e2", "INFY", "SELL", 50)

    # Net negative position
    store.process_event("e3", "TCS", "SELL", 75)

    positions = store.get_positions()
    assert positions["INFY"] == 0
    assert positions["TCS"] == -75


def test_multiple_symbols_and_case_preservation():
    store = PositionStore()
    store.process_event("e1", "RELIANCE", "BUY", 100)
    store.process_event("e2", "reliance", "BUY", 50)  # Lowercase symbol
    store.process_event("e3", "TCS", "SELL", 25)

    positions = store.get_positions()
    assert positions == {
        "RELIANCE": 100,
        "reliance": 50,
        "TCS": -25
    }


def test_duplicate_event_id_handling():
    store = PositionStore()

    # First event wins
    processed1, duplicate1 = store.process_event("e1", "RELIANCE", "BUY", 100)
    assert processed1 is True
    assert duplicate1 is False

    # Second event with same event_id ignored even if parameters differ
    processed2, duplicate2 = store.process_event("e1", "RELIANCE", "BUY", 999)
    assert processed2 is False
    assert duplicate2 is True

    # Position remains 100
    assert store.get_positions() == {"RELIANCE": 100}


def test_concurrent_position_updates():
    store = PositionStore()
    threads = []
    num_threads = 20
    events_per_thread = 50

    def worker(thread_idx):
        for i in range(events_per_thread):
            event_id = f"t{thread_idx}-e{i}"
            store.process_event(event_id, "CONCURRENCY_TEST", "BUY", 1)

    for idx in range(num_threads):
        t = threading.Thread(target=worker, args=(idx,))
        threads.append(t)
        t.start()

    for t in threads:
        t.join()

    expected_qty = num_threads * events_per_thread
    assert store.get_positions() == {"CONCURRENCY_TEST": expected_qty}
