import tempfile
import pytest
from unittest.mock import patch, MagicMock
from order_service.service import OrderUpdateService


def test_order_service_skips_invalid_and_processes_valid_rows():
    csv_content = """event_id,symbol,transaction_type,quantity
evt-001,RELIANCE,BUY,100
evt-invalid-1,,BUY,50
evt-invalid-2,TCS,INVALID_TYPE,50
evt-invalid-3,TCS,BUY,0
evt-invalid-4,TCS,BUY,-10
evt-invalid-5,TCS,BUY,abc
evt-002,TCS,SELL,75
"""
    with tempfile.NamedTemporaryFile(mode="w+", delete=False, suffix=".csv") as tmp:
        tmp.write(csv_content)
        tmp.flush()
        tmp_path = tmp.name

    service = OrderUpdateService(csv_path=tmp_path, target_url="http://mock-target/events", rate_limit=100.0)

    mock_response = MagicMock()
    mock_response.status_code = 200
    mock_response.json.return_value = {"status": "accepted"}

    with patch("requests.post", return_value=mock_response) as mock_post:
        stats = service.process()

        assert stats["total_rows"] == 7
        assert stats["accepted_valid"] == 2
        assert stats["rejected_invalid"] == 5
        assert stats["successfully_sent"] == 2
        assert mock_post.call_count == 2


def test_order_service_rate_throttling():
    csv_content = """event_id,symbol,transaction_type,quantity
evt-001,RELIANCE,BUY,100
evt-002,TCS,SELL,75
"""
    with tempfile.NamedTemporaryFile(mode="w+", delete=False, suffix=".csv") as tmp:
        tmp.write(csv_content)
        tmp.flush()
        tmp_path = tmp.name

    # Rate limit of 5 events/sec -> 0.2s interval
    service = OrderUpdateService(csv_path=tmp_path, target_url="http://mock-target/events", rate_limit=5.0)

    mock_response = MagicMock()
    mock_response.status_code = 200
    mock_response.json.return_value = {"status": "accepted"}

    with patch("requests.post", return_value=mock_response), patch("time.sleep") as mock_sleep:
        service.process()
        # Sleep should be invoked at least once for throttling between events
        assert mock_sleep.called
