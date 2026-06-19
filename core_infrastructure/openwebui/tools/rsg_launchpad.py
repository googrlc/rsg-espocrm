"""
title: RSG Launchpad
author: RSG
version: 0.1.0
description: Point users to the RSG agency launchpad (categorized carrier/MGA/GA login links, per-agent favorites, add/edit/remove).
"""
class Tools:
    def get_launchpad_url(self) -> str:
        """
        Return the RSG agency launchpad URL. Use when someone asks for the login page,
        carrier links, or where to manage agency logins/favorites.
        :return: The launchpad URL (open in a browser).
        """
        return "https://openwebui-l8ola-u69864.vm.elestio.app/static/rsg-launchpad.html"
