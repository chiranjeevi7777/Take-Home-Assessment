import logging
import os
import argparse
from fastapi import FastAPI, HTTPException, status
from pydantic import BaseModel, Field
from position_service.store import PositionStore

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s"
)
logger = logging.getLogger("PositionMaintainingService")

app = FastAPI(
    title="Position Maintaining Service",
    description="Maintains net position for trading symbols and ensures idempotency.",
    version="1.0.0"
)

# Global in-memory position store instance
store = PositionStore()


class OrderEvent(BaseModel):
    event_id: str = Field(..., min_length=1, description="Unique event identifier")
    symbol: str = Field(..., min_length=1, description="Trading symbol (case preserved)")
    transaction_type: str = Field(..., description="BUY or SELL")
    quantity: int = Field(..., gt=0, description="Positive integer quantity")


@app.post("/events", status_code=status.HTTP_200_OK)
def receive_event(event: OrderEvent):
    """
    Receives an order update event and updates net position idempotently.
    """
    # Strict validation check for transaction type
    if event.transaction_type not in ("BUY", "SELL"):
        logger.warning(f"Rejected event {event.event_id}: Invalid transaction_type '{event.transaction_type}'")
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"transaction_type must be BUY or SELL, got '{event.transaction_type}'"
        )

    processed, is_duplicate = store.process_event(
        event_id=event.event_id,
        symbol=event.symbol,
        transaction_type=event.transaction_type,
        quantity=event.quantity
    )

    if is_duplicate:
        logger.info(f"Duplicate event ignored: event_id='{event.event_id}'")
        return {"status": "ignored_duplicate", "event_id": event.event_id}

    logger.info(
        f"Event accepted: event_id='{event.event_id}', symbol='{event.symbol}', "
        f"type='{event.transaction_type}', quantity={event.quantity}"
    )
    return {"status": "accepted", "event_id": event.event_id}


@app.get("/position")
def get_positions():
    """
    Returns the current net position for every symbol seen in an accepted event,
    including symbols whose net position is zero or negative.
    """
    positions = store.get_positions()
    logger.info(f"Position snapshot requested. Returning positions for {len(positions)} symbols.")
    return positions


@app.get("/health")
def health_check():
    """
    Health check endpoint.
    """
    return {"status": "healthy"}


def main():
    import uvicorn
    parser = argparse.ArgumentParser(description="Position Maintaining Service")
    parser.add_argument("--host", default=os.getenv("HOST", "0.0.0.0"), help="Host IP to bind (default: 0.0.0.0)")
    parser.add_argument("--port", type=int, default=int(os.getenv("PORT", "8000")), help="Port to bind (default: 8000)")
    args = parser.parse_args()

    logger.info(f"Starting Position Maintaining Service on {args.host}:{args.port}")
    uvicorn.run(app, host=args.host, port=args.port)


if __name__ == "__main__":
    main()
