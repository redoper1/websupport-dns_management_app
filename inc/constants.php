<?php
const DNS_RECORD_TYPES = [
    'A' => ['type', 'name', 'content', 'ttl', 'note'],
    'AAAA' => ['type', 'name', 'content', 'ttl', 'note'],
    'ANAME' => ['type', 'name', 'content', 'ttl', 'note'],
    'CNAME' => ['type', 'name', 'content', 'ttl', 'note'],
    'MX' => ['type', 'name', 'content', 'prio', 'ttl', 'note'],
    'NS' => ['type', 'name', 'content', 'ttl', 'note'],
    'SRV' => ['type', 'name', 'content', 'prio', 'port', 'weight', 'ttl', 'note'],
    'TXT' => ['type', 'name', 'content', 'ttl', 'note'],
];
