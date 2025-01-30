# Cloudinary Video Grid for WordPress

A WordPress plugin that creates a filterable masonry grid of videos from your Cloudinary folders. Features an easy-to-use shortcode system and filter buttons for users and technologies.

## Features

- Masonry grid layout for responsive video display
- Automatic video fetching from Cloudinary folders
- Filterable grid with user and technology categories
- 50x50px filter buttons in a horizontal layout
- Easy-to-use shortcode system
- Secure API integration with Cloudinary
- WordPress admin panel integration

## Installation

1. Download the plugin files and upload them to your `/wp-content/plugins/cloudinary-video-grid` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Cloudinary Video Grid to configure your Cloudinary API credentials

## Configuration

### Admin Settings
Navigate to Settings > Cloudinary Video Grid in your WordPress admin panel and enter:
- Cloud Name
- API Key
- API Secret

You can find these credentials in your Cloudinary Dashboard.

### Usage

Use the shortcode with your folder name to display the video grid:

```php
[cloudinary_video_grid folder="your-folder-name"]
```

## Filter Categories

### Users
- User 1
- User 2
- User 3

### Technologies
- AR
- VR
- AI
- JS
- Cesium

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Active Cloudinary account with API credentials

## Support

For support, please create an issue in the GitHub repository or contact the plugin maintainer.

## Development

### Building from Source

1. Clone the repository
2. Install dependencies (if any)
3. Make your modifications
4. Test thoroughly before deployment

### Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

Distributed under the GPL v2 or later. See LICENSE for more information.

## Changelog

### 1.0.0
- Initial release
- Basic masonry grid functionality
- Cloudinary folder integration
- Filter system implementation