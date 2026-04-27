<?php

/*
 * Constants for identifying bot/spam accounts based on analysis of signup data (2022–2026).
 *
 * BAD_EMAIL_DOMAINS: Domain names associated with spam/bot registrations.
 *   - Japanese bot farm and drug trafficking entries are subdomain patterns: match with LIKE '%.domain'
 *   - All other entries are exact domains: match with LIKE '%@domain'
 *
 * BAD_WORDS: Terms and URL fragments found in bot usernames, guild names, and server names.
 */

// Bad email domains identified from bot/spam registrations.
const BAD_EMAIL_DOMAINS = [
    // Japanese-name bot farm, 2022 infra (subdomain pattern)
    'officemail.fun',
    'inwebmail.fun',
    'meta1.in.net',
    'officemail.in.net',
    'sorataki.in.net',
    'kiyoakari.xyz',

    // Japanese-name bot farm, 2023-2026 rotated infra (subdomain pattern)
    'flooz.site',
    'drkoop.site',
    'excitemail.fun',
    'infoseekmail.online',
    'gcpmail1.site',
    'mailguard.space',
    'mailscan.site',
    'mailvista.site',
    'webvan.site',
    'infospace.fun',

    // Drug trafficking SEO backlink campaign (subdomain pattern)
    'kypit-kokain-v-ukraine.space',
    'cocaine-kypit-ukraine.shop',
    'cocaine-in-kyiv.online',
    'cocaines-kyiv.online',
    'cocaine-v-toshkente.shop',
    'cocaine-v-ukraine.shop',
    'cocaine-moscow-russia.shop',
    'russia-cocaine.online',
    'russia-cocaine-kypit.online',
    'kypit-kokain-moscow.ru',
    'kypit-in-ukraine.site',
    'kypit-belii-bilet.online',
    'inrus.top',

    // BTC URL-injection bot relay domains
    'code-gmail.com',
    'setxko.com',
    'murahpanel.com',
    'couxpn.com',
    'eewmaop.com',
    'omggreatfoods.com',
    '24hinbox.com',
    'timhoreads.com',
    'automisly.org',
    'ecocryptolab.com',
    'angga.team',
    'twitch.work',
    'gmailbrt.com',

    // Spam relay infrastructure
    'pifpaf.space',
    'bumss.fun',
    'mailsrp.com',
    'mailserp.com',
    'vmailer.site',
    'vmailer.ru',
    'xrust.club',
    'dimail.xyz',
    'boxomail.live',
    'dynainbox.com',
    'rightbliss.beauty',
    'silesia.life',
    'dianabykiris.fun',
    'top-21.online',

    // Crypto spam
    'gwmetabitt.com',
    'bitcoinblaster.pro',

    // Counterfeit goods SEO
    'uggs-sale.store',
    'uggs-sale.ru',

    // Dating / adult spam
    'andreicutie.com',
    'massagefin.site',

    // Typosquats on legitimate providers
    'hotmails.com',
    'gmailwe.com',

    // Miscellaneous spam
    'filmkachat.ru',
    'vdnh.online',
    'suttal.com',
    'suprb.site',
    'skipadoo.org',
    'phtunneler.com',
];

// Bad words and patterns found in bot usernames, guild names, and server names.
const BAD_WORDS = [
    // Pharma SEO spam — found in guild names (case-insensitive match)
    'viagra',
    'cialis',
    'tadalafil',
    'pfizer',
    'viagramg',
    'viagraenligne',
    'tadalafilrx',
    'tadalafilenligne',
    'prixviagra',
    'pfizerviagra',
    'vidokingooo',

    // URL injection — found in usernames; no legitimate username contains these
    'http://',
    'https://',
    'www.',
    't.me/',

    // BTC campaign — tracking hash present in every 2024-2026 BTC injection username
    'hs=cefda51815b25f1040282bdfb4523fc2',

    // BTC campaign — link domains used in injection messages
    'telegra.ph',
    'graph.org',
];
