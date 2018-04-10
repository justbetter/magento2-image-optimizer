# Magento 2 Image Optimizer

This Magento 2 module is a wrapper based on the package [Spatie Image optimizer](https://github.com/spatie/image-optimizer). 

## Installation
- `composer require justbetter/magento2-image-optimizer`
- `bin/magento module:enable JustBetter_ImageOptimizer`
- `bin/magento setup:upgrade && bin/magento setup:static-content:deploy`

### Optimization tools

The package will use these optimizers if they are present on your system:

- [JpegOptim](http://freecode.com/projects/jpegoptim)
- [Optipng](http://optipng.sourceforge.net/)
- [Pngquant 2](https://pngquant.org/)
- [SVGO](https://github.com/svg/svgo)
- [Gifsicle](http://www.lcdf.org/gifsicle/)

Here's how to install all the optimizers on Ubuntu:

```bash
sudo apt-get install jpegoptim
sudo apt-get install optipng
sudo apt-get install pngquant
sudo npm install -g svgo
sudo apt-get install gifsicle
```

And here's how to install the binaries on MacOS (using [Homebrew](https://brew.sh/)):

```bash
brew install jpegoptim
brew install optipng
brew install pngquant
brew install svgo
brew install gifsicle
```

## Configuration
- Options for the module are defined in the backend under Stores > Configuration > JustBetter > Image optimizer configuration.
- Currently the only option available is to log the compression.

## Compatibility
The module is tested on magento version 2.2.x with Spatie image optimizer version 1.0.x

## Ideas, bugs or suggestions?
Please create a [issue](https://github.com/justbetter/magento2-image-optimizer/issues) or a [pull request](https://github.com/justbetter/magento2-image-optimizer/pulls).

## Todo
- Configurable options for compression
- Compress all library images in console command

## License
[MIT](LICENSE)

---

<a href="https://justbetter.nl" title="JustBetter"><img src="https://raw.githubusercontent.com/justbetter/art/master/justbetter-logo.png" width="200px" alt="JustBetter logo"></a>
