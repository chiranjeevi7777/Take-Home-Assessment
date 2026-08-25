import csv
import time
import logging
import argparse
import os
import requests
from typing import Dict, Any
from order_service.validator import validate_row

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s"
)
logger = logging.getLogger("OrderUpdateService")


class OrderUpdateService:
    """
    Service that reads an order updates CSV incrementally, validates rows,
    throttles event emission rate, and sends valid events to the Position Maintaining Service.
    """
    def __init__(self, csv_path: str, target_url: str, rate_limit: float = 50.0, timeout: float = 5.0):
        self.csv_path = csv_path
        self.target_url = target_url
        self.rate_limit = rate_limit  # Max events per second
        self.min_interval = 1.0 / rate_limit if rate_limit > 0 else 0.0
        self.timeout = timeout

    def process(self) -> Dict[str, int]:
        if not os.path.exists(self.csv_path):
            logger.error(f"Input CSV file does not exist: {self.csv_path}")
            raise FileNotFoundError(f"Input CSV file not found: {self.csv_path}")

        stats = {
            "total_rows": 0,
            "accepted_valid": 0,
            "rejected_invalid": 0,
            "successfully_sent": 0,
            "failed_send": 0
        }

        logger.info(
            f"Starting CSV processing from '{self.csv_path}' -> Target: '{self.target_url}' "
            f"(Rate limit: {self.rate_limit} events/sec)"
        )

        last_send_time = 0.0

        with open(self.csv_path, mode="r", encoding="utf-8-sig") as f:
            reader = csv.DictReader(f)
            for row_num, row in enumerate(reader, start=1):
                stats["total_rows"] += 1
                is_valid, event_data, error_reason = validate_row(row)

                if not is_valid:
                    stats["rejected_invalid"] += 1
                    logger.warning(
                        f"Row #{row_num} REJECTED: {error_reason} | Content: {row}"
                    )
                    continue

                stats["accepted_valid"] += 1
                logger.info(
                    f"Row #{row_num} ACCEPTED: event_id={event_data['event_id']}, "
                    f"symbol={event_data['symbol']}, type={event_data['transaction_type']}, qty={event_data['quantity']}"
                )

                # Rate limit throttling
                now = time.time()
                elapsed = now - last_send_time
                if elapsed < self.min_interval:
                    time.sleep(self.min_interval - elapsed)

                # Transmit event over HTTP
                try:
                    resp = requests.post(self.target_url, json=event_data, timeout=self.timeout)
                    if resp.status_code == 200:
                        stats["successfully_sent"] += 1
                        last_send_time = time.time()
                        logger.info(
                            f"Event '{event_data['event_id']}' successfully sent to target. "
                            f"Response: {resp.json()}"
                        )
                    else:
                        stats["failed_send"] += 1
                        logger.error(
                            f"Event '{event_data['event_id']}' send failed with status {resp.status_code}: {resp.text}"
                        )
                except Exception as e:
                    stats["failed_send"] += 1
                    logger.error(f"Connection error sending event '{event_data['event_id']}': {e}")

        logger.info(
            f"Input processing complete. Final Statistics: "
            f"Total={stats['total_rows']}, Valid={stats['accepted_valid']}, "
            f"Invalid={stats['rejected_invalid']}, Sent={stats['successfully_sent']}, "
            f"Failed={stats['failed_send']}"
        )
        return stats


def main():
    parser = argparse.ArgumentParser(description="Order Update Service")
    parser.add_argument(
        "--csv-path",
        default=os.getenv("CSV_PATH", "order_updates.csv"),
        help="Path to the order updates CSV file"
    )
    parser.add_argument(
        "--target-url",
        default=os.getenv("TARGET_URL", "http://localhost:8000/events"),
        help="Target Position Maintaining Service endpoint URL"
    )
    parser.add_argument(
        "--rate-limit",
        type=float,
        default=float(os.getenv("RATE_LIMIT", "50.0")),
        help="Maximum events per second to emit (default: 50.0)"
    )
    args = parser.parse_args()

    service = OrderUpdateService(
        csv_path=args.csv_path,
        target_url=args.target_url,
        rate_limit=args.rate_limit
    )
    service.process()


if __name__ == "__main__":
    main()
