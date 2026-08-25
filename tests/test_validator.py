import pytest
from order_service.validator import validate_row


def test_valid_buy_and_sell_rows():
    valid_buy = {"event_id": "evt-001", "symbol": "RELIANCE", "transaction_type": "BUY", "quantity": "100"}
    is_valid, parsed, err = validate_row(valid_buy)
    assert is_valid is True
    assert err is None
    assert parsed == {
        "event_id": "evt-001",
        "symbol": "RELIANCE",
        "transaction_type": "BUY",
        "quantity": 100
    }

    valid_sell = {"event_id": "evt-002", "symbol": "tcs", "transaction_type": "SELL", "quantity": "50"}
    is_valid, parsed, err = validate_row(valid_sell)
    assert is_valid is True
    assert err is None
    assert parsed["symbol"] == "tcs"  # Case preserved
    assert parsed["transaction_type"] == "SELL"
    assert parsed["quantity"] == 50


def test_blank_event_id():
    row = {"event_id": "   ", "symbol": "RELIANCE", "transaction_type": "BUY", "quantity": "10"}
    is_valid, parsed, err = validate_row(row)
    assert is_valid is False
    assert "event_id" in err.lower()

    row_missing = {"symbol": "RELIANCE", "transaction_type": "BUY", "quantity": "10"}
    is_valid, parsed, err = validate_row(row_missing)
    assert is_valid is False
    assert "event_id" in err.lower()


def test_blank_symbol():
    row = {"event_id": "evt-001", "symbol": "", "transaction_type": "BUY", "quantity": "10"}
    is_valid, parsed, err = validate_row(row)
    assert is_valid is False
    assert "symbol" in err.lower()


def test_invalid_transaction_types():
    for invalid_type in ["HOLD", "buy", "SELL ", "TRANSFER", "123"]:
        row = {"event_id": "evt-001", "symbol": "RELIANCE", "transaction_type": invalid_type, "quantity": "10"}
        is_valid, parsed, err = validate_row(row)
        assert is_valid is False, f"Should reject invalid transaction_type: {invalid_type}"
        assert "transaction_type" in err.lower()


def test_zero_negative_non_integer_and_blank_quantities():
    invalid_quantities = [
        "0",          # Zero
        "-10",        # Negative
        "10.5",       # Non-integer float
        "abc",        # String text
        "",           # Blank
        "   ",        # Whitespace
        None          # Missing
    ]

    for qty in invalid_quantities:
        row = {"event_id": "evt-001", "symbol": "RELIANCE", "transaction_type": "BUY", "quantity": qty}
        is_valid, parsed, err = validate_row(row)
        assert is_valid is False, f"Should reject invalid quantity: {qty}"
        assert "quantity" in err.lower()
