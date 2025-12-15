<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HuntFeed – PHP RSS Feed Library with Real-Time WebSub Support</title>

    <meta name="description" content="HuntFeed is a fast, open-source PHP RSS feed library with real-time WebSub support. Build RSS aggregators, APIs, and bots with instant updates.">
    <meta name="keywords" content="PHP RSS feed library, PHP RSS parser, real-time RSS PHP, WebSub PHP library, PubSubHubbub PHP, RSS aggregator PHP">
    <meta name="author" content="Hossein Mohmmadian">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:title" content="HuntFeed – Real-Time PHP RSS Feed Library">
    <meta property="og:description" content="Open-source PHP RSS & Atom feed library with real-time WebSub (PubSubHubbub) support.">
    <meta property="og:image" content="https://hosseinhunta.github.io/huntfeed/images/huntfeed-logo.png">
    <meta property="og:url" content="https://hosseinhunta.github.io/huntfeed/">
    <meta property="og:type" content="website">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "HuntFeed",
      "description": "Open-source PHP RSS feed library with real-time WebSub support",
      "applicationCategory": "DeveloperApplication",
      "operatingSystem": "Any",
      "softwareVersion": "1.0.0",
      "url": "https://hosseinhunta.github.io/huntfeed/",
      "codeRepository": "https://github.com/hosseinhunta/huntfeed",
      "sameAs": [
        "https://github.com/hosseinhunta/huntfeed",
        "https://packagist.org/packages/hosseinhunta/huntfeed"
      ],
      "author": {
        "@type": "Person",
        "name": "Hossein Mohmmadian"
      },
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
      }
    }
    </script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#">HuntFeed</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="#installation">Installation</a></li>
        <li class="nav-item"><a class="nav-link" href="#examples">Examples</a></li>
        <li class="nav-item"><a class="nav-link" href="https://github.com/hosseinhunta/huntfeed" target="_blank">GitHub</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="hero-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h1>HuntFeed – Real-Time PHP RSS Feed Library</h1>
        <h2 class="h5 mt-3 text-light">Open-source PHP library for RSS, Atom & JSON Feed with WebSub (PubSubHubbub)</h2>
        <p class="lead mt-4">Build high-performance RSS aggregators, APIs, and bots using real-time feed updates instead of inefficient polling.</p>
        <a href="#installation" class="btn btn-primary btn-lg me-2">Get Started</a>
        <a href="https://github.com/hosseinhunta/huntfeed" class="btn btn-outline-light btn-lg" target="_blank">GitHub</a>
      </div>
      <div class="col-lg-6">
        <pre><code class="language-php">&lt;?php
use Hosseinhunta\Huntfeed\Hub\FeedManager;

$manager = new FeedManager();
$manager->registerFeed('tech','https://news.ycombinator.com/rss');

$manager->on('item:new', fn($d) => notify($d['item']));

echo $manager->export('json');
?&gt;</code></pre>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white">
  <div class="container">
    <h2>PHP RSS Feed Library for Real-Time Applications</h2>
    <p>HuntFeed is a modern <strong>PHP RSS feed library</strong> designed for developers who need instant updates, scalability, and clean architecture. Unlike traditional PHP RSS parsers, HuntFeed uses <strong>WebSub</strong> for real-time push notifications.</p>
    <p>Use HuntFeed to build news aggregators, Telegram bots, REST APIs, monitoring tools, and content platforms.</p>
  </div>
</section>

<section id="features" class="py-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-5">Why HuntFeed?</h2>
    <div class="row g-4">
      <div class="col-md-4"><div class="feature-card"><h3>Real-Time RSS</h3><p>WebSub support for instant updates without polling.</p></div></div>
      <div class="col-md-4"><div class="feature-card"><h3>Multi-Format</h3><p>RSS, Atom, JSON Feed, RDF, GeoRSS.</p></div></div>
      <div class="col-md-4"><div class="feature-card"><h3>Production Ready</h3><p>Secure, scalable, and event-driven.</p></div></div>
    </div>
  </div>
</section>

<section id="installation" class="py-5">
  <div class="container">
    <h2 class="text-center mb-4">Installation</h2>
    <pre><code class="language-bash">composer require hosseinhunta/huntfeed</code></pre>
  </div>
</section>

<footer class="bg-dark text-white py-4">
  <div class="container text-center">
    <p>© 2024 HuntFeed – Open-source PHP RSS Feed Library</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
</body>
</html>
