"""
Shared urllib helpers for calls from containers to the Laravel app on the host.

When Laravel is served via HTTPS with a local certificate (e.g. Herd), set
LARAVEL_INTERNAL_SSL_INSECURE=1 in microservices/.env so downloads/API calls succeed
from Docker. Do not enable that in production.
"""

from __future__ import annotations

import os
import ssl
from urllib import request as urlrequest
from urllib.request import Request

__all__ = ["urlopen_internal"]


def urlopen_internal(req: Request, *, timeout: int):
    insecure = os.getenv("LARAVEL_INTERNAL_SSL_INSECURE", "").strip().lower() in (
        "1",
        "true",
        "yes",
    )
    if insecure:
        ctx = ssl._create_unverified_context()
        return urlrequest.urlopen(req, timeout=timeout, context=ctx)
    return urlrequest.urlopen(req, timeout=timeout)
