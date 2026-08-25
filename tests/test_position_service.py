import pytest
from fastapi.testclient import TestClient
from position_service.app import app, store

client = TestClient(app)


@pytest.fixture(autouse=True)
def reset_store():
    store.clear()
    yield
    store.clear()


def test_post_valid_event():
    payload = {
        "event_id": "evt-001",
        "symbol": "RELIANCE",
        "transaction_type": "BUY",
        "quantity": 90
    }
    response = client.post("/events", json=payload)
    assert response.status_code == 200
    assert response.json() == {"status": "accepted", "event_id": "evt-001"}

    pos_resp = client.get("/position")
    assert pos_resp.status_code == 200
    assert pos_resp.json() == {"RELIANCE": 90}


def test_post_duplicate_event():
    payload = {
        "event_id": "evt-001",
        "symbol": "RELIANCE",
        "transaction_type": "BUY",
        "quantity": 90
    }
    resp1 = client.post("/events", json=payload)
    assert resp1.status_code == 200

    # Post duplicate event with different quantity
    payload_dup = {
        "event_id": "evt-001",
        "symbol": "RELIANCE",
        "transaction_type": "SELL",
        "quantity": 500
    }
    resp2 = client.post("/events", json=payload_dup)
    assert resp2.status_code == 200
    assert resp2.json() == {"status": "ignored_duplicate", "event_id": "evt-001"}

    # Position should still reflect original event
    pos_resp = client.get("/position")
    assert pos_resp.json() == {"RELIANCE": 90}


def test_post_invalid_transaction_type():
    payload = {
        "event_id": "evt-002",
        "symbol": "TCS",
        "transaction_type": "INVALID_TYPE",
        "quantity": 50
    }
    response = client.post("/events", json=payload)
    assert response.status_code == 400
    assert "transaction_type" in response.json()["detail"]


def test_post_invalid_quantity():
    payload = {
        "event_id": "evt-003",
        "symbol": "TCS",
        "transaction_type": "BUY",
        "quantity": -10
    }
    response = client.post("/events", json=payload)
    assert response.status_code == 422  # Pydantic validation error gt=0


def test_get_position_endpoint():
    client.post("/events", json={"event_id": "e1", "symbol": "RELIANCE", "transaction_type": "BUY", "quantity": 100})
    client.post("/events", json={"event_id": "e2", "symbol": "TCS", "transaction_type": "SELL", "quantity": 75})
    client.post("/events", json={"event_id": "e3", "symbol": "INFY", "transaction_type": "BUY", "quantity": 50})
    client.post("/events", json={"event_id": "e4", "symbol": "INFY", "transaction_type": "SELL", "quantity": 50})

    response = client.get("/position")
    assert response.status_code == 200
    assert response.json() == {
        "RELIANCE": 100,
        "TCS": -75,
        "INFY": 0
    }
