import time
import tempfile
import threading
import uvicorn
import requests
import pytest
from position_service.app import app, store
from order_service.service import OrderUpdateService


class UvicornServer(threading.Thread):
    def __init__(self, app, host="127.0.0.1", port=8999):
        super().__init__()
        self.server = uvicorn.Server(
            config=uvicorn.Config(app, host=host, port=port, log_level="warning")
        )
        self.host = host
        self.port = port

    def run(self):
        self.server.run()

    def stop(self):
        self.server.should_exit = True


def test_end_to_end_integration():
    store.clear()
    server = UvicornServer(app, host="127.0.0.1", port=8999)
    server.start()

    # Wait for server startup
    target_url = "http://127.0.0.1:8999/events"
    position_url = "http://127.0.0.1:8999/position"
    
    for _ in range(50):
        try:
            resp = requests.get("http://127.0.0.1:8999/health", timeout=1.0)
            if resp.status_code == 200:
                break
        except Exception:
            time.sleep(0.1)

    try:
        # Create synthetic test CSV with valid, invalid, duplicate, negative, and zero positions
        csv_content = """event_id,symbol,transaction_type,quantity
evt-0001,RELIANCE,BUY,100
evt-0002,TCS,SELL,75
evt-0003,INFY,BUY,50
evt-0004,INFY,SELL,50
evt-invalid-1,,BUY,10
evt-invalid-2,HDFCBANK,INVALID_TYPE,20
evt-invalid-3,SBIN,BUY,0
evt-invalid-4,SBIN,BUY,-5
evt-invalid-5,SBIN,BUY,non_int
evt-0005,RELIANCE,SELL,20
evt-0001,RELIANCE,BUY,9999
evt-0006,TCS,SELL,25
"""
        with tempfile.NamedTemporaryFile(mode="w+", delete=False, suffix=".csv") as tmp:
            tmp.write(csv_content)
            tmp.flush()
            tmp_path = tmp.name

        order_service = OrderUpdateService(
            csv_path=tmp_path,
            target_url=target_url,
            rate_limit=100.0
        )
        stats = order_service.process()

        assert stats["total_rows"] == 12
        assert stats["accepted_valid"] == 7
        assert stats["rejected_invalid"] == 5
        assert stats["successfully_sent"] == 7

        # Verify Positions via GET /position
        pos_resp = requests.get(position_url, timeout=5.0)
        assert pos_resp.status_code == 200
        positions = pos_resp.json()

        # Expected positions:
        # RELIANCE: +100 -20 = 80 (evt-0001 duplicate 9999 ignored!)
        # TCS: -75 -25 = -100
        # INFY: +50 -50 = 0
        assert positions == {
            "RELIANCE": 80,
            "TCS": -100,
            "INFY": 0
        }

    finally:
        server.stop()
        server.join()
        store.clear()
