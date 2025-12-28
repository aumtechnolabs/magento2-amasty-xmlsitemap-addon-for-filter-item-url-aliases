
Aum Technolabs — Magento 2 integration for Amasty XML Sitemap

Overview
-
This Magento 2 module extends the Amasty XML Sitemap generator to include filter-item URLs on category pages when a URL alias exists. It ensures those filter-applied pages (with SEO-friendly URL aliases) are added to the sitemap so search engines can discover and index them, improving site SEO and crawl coverage.

Key features
-
- Adds filter-item (filtered category) URLs to Amasty XML Sitemap when URL aliases are present
- Keeps sitemap generation compatible with Amasty's XML Sitemap module
- Lightweight, non-intrusive cron customization for environments that require an alternate trigger

Compatibility
-
- Magento 2.x
- Amasty XML Sitemap module (required)

Installation
-
1. Copy the module into your Magento installation under app/code/AumTechnolabs/AmastyURLAliasToSiteMap.
2. Run Magento upgrade and deploy commands:

```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```

Configuration & Usage
-
- Ensure the Amasty XML Sitemap module is installed and configured.
- This module augments Amasty's sitemap generation to include filter-item URLs when a matching URL alias exists.
- By default the module integrates with Amasty's cron-based sitemap generation. If your environment requires custom cron behavior, a helper script (`AMASTY_XML_SITEMAP_GENERATOR_CRON.php`) is provided in this package and can be invoked by your server cron or an external HTTP trigger.

Cron file and placement
-
Place the `AMASTY_XML_SITEMAP_GENERATOR_CRON.php` file in Magento's `pub/` directory and ensure it is reachable via your site URL (for example `https://www.example.com/AMASTY_XML_SITEMAP_GENERATOR_CRON.php`). This module replaces Amasty's built-in cron trigger in favor of a simple HTTP-triggered script when environments require it; the module's `GenerateSitemap::execute()` method returns early and sitemap generation is performed using `generateSitemapIdWise($sitemapId)`.

Example cron (server-side) using `curl`:

```cron
50 0 * * * /usr/bin/curl https://www.some-site.com/AMASTY_XML_SITEMAP_GENERATOR_CRON.php
```

Notes
-
- The provided `AMASTY_XML_SITEMAP_GENERATOR_CRON.php` is intended only for environments where the default Amasty cron integration is not usable. The module disables Amasty's `crontab.xml` in favor of this approach — see `Model/Cron/GenerateSitemap.php` for context and the in-file comment explaining why the HTTP trigger is used.
- Ensure proper permissions and security for the file in `pub/` (restrict access if necessary or protect with a secret token if exposing publicly).

Manual sitemap generation
-
If you prefer to trigger sitemap generation for a specific sitemap programmatically, the module exposes a helper method `generateSitemapIdWise($sitemapId)` in the cron class. This can be used by custom scripts or controllers to generate a single sitemap by id.

Troubleshooting
-
- If filtered URLs are not appearing in the sitemap, verify that URL aliases exist for the filtered pages and that Amasty XML Sitemap is active.
- Check var/log and Magento system logs for errors during sitemap generation.
- Ensure cron or your alternative trigger is running and able to reach `AMASTY_XML_SITEMAP_GENERATOR_CRON.php` if using the provided script.

Contributing and Support
-
Contributions, bug reports, and feature requests are welcome. Please open issues or pull requests via the repository hosting this module.

Author
-
Aum Technolabs — Magento 2 customizations and consulting.

License
-
See the included LICENSE file for licensing details.
