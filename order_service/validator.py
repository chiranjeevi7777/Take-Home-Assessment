import logging
from typing import Dict, Any, Tuple, Optional

logger = logging.getLogger("order_validator")


def validate_row(row: Dict[str, Any]) -> Tuple[bool, Optional[Dict[str, Any]], Optional[str]]:
    """
    Validates a raw CSV row dictionary according to the Event Contract:
    - event_id: non-empty string, uniquely identifies an event.
    - symbol: non-empty string. Preserve supplied case and value.
    - transaction_type: must be exactly BUY or SELL.
    - quantity: must be a positive integer (> 0).

    Returns:
        (is_valid, parsed_event_dict, error_reason)
    """
    if not isinstance(row, dict):
        return False, None, "Row must be a dictionary"

    # 1. Validate event_id
    raw_event_id = row.get("event_id")
    if raw_event_id is None or not str(raw_event_id).strip():
        return False, None, "Blank or missing event_id"
    event_id = str(raw_event_id).strip()

    # 2. Validate symbol
    raw_symbol = row.get("symbol")
    if raw_symbol is None or not str(raw_symbol).strip():
        return False, None, "Blank or missing symbol"
    symbol = str(raw_symbol).strip()

    # 3. Validate transaction_type (must be exactly BUY or SELL)
    raw_tx_type = row.get("transaction_type")
    if raw_tx_type is None or str(raw_tx_type) not in ("BUY", "SELL"):
        return False, None, f"Invalid transaction_type '{raw_tx_type}'. Must be exactly BUY or SELL"
    transaction_type = str(raw_tx_type)

    # 4. Validate quantity
    raw_qty = row.get("quantity")
    if raw_qty is None or not str(raw_qty).strip():
        return False, None, "Blank or missing quantity"

    qty_str = str(raw_qty).strip()
    try:
        # Check strict integer format (disallow float decimals like 10.5 or 10.0)
        qty = int(qty_str)
        if str(qty) != qty_str:
            return False, None, f"Non-integer quantity '{raw_qty}'"
    except (ValueError, TypeError):
        return False, None, f"Non-integer quantity '{raw_qty}'"

    if qty <= 0:
        return False, None, f"Quantity must be positive (> 0), got {qty}"

    return True, {
        "event_id": event_id,
        "symbol": symbol,
        "transaction_type": transaction_type,
        "quantity": qty
    }, None
