# C2C Temperature Blanket

## Up and Running

### The Less-technical Method

1. Get the code onto your computer
    1. Click the green `Code` button at the top of the page
    2. Click the `Download ZIP` link
    3. Unzip the file on your computer
2. Running the code
    1. Open the `terminal` application on your computer
    2. Run `php artisan serve`
    3. Point your browser to http://localhost:8080

### The More-technical Method

1. Get the code onto your computer
    1. Open the `terminal` application on your computer
    2. `composer create-project skybluesofa/c2c-temperature-blanket example-app` where "example-app" is the name of the folder you'll be creating. A suggestion would be 'c2c-temperature-blanket'.
2. Using Docker (Optional)
    1. `docker-compose build`
    2. `docker-compose up -d`
3. Using Built-in PHP Server (alternate)
    1. Run `php artisan serve`
    3. Point your browser to http://localhost:8080

## Setup

### The .env File

In the installation folder, there is a file named `.env.example`. It should be copied and renamed `.env`.

In the `.env` file, you'll find the configuration options for the application. The options specific to Temperature Blanket are:

- `C2C_BLANKET_LATITUDE=38.6270` This is the latitude of your location, used to pinpoint your weather.
- `C2C_BLANKET_LONGITUDE=-90.1994` This is the longitude of your location, used to pinpoint your weather.
- `C2C_BLANKET_COLUMNS=16` This is the number of squares your blanket will have per row.
- `C2C_BLANKET_DESIGN=default` This is the design scheme designation for your blanket squares.
- `C2C_BLANKET_COLORS=default` This is the color scheme designation for the tiles of the design squares.

### The Configuration File

In the `config` folder, there is a file named `c2c.php`. This file contains configuration options such as:

#### Square Design. There are some built-in designs available:
- **Default.** A 3x3 square with all tiles being the day's average temperature
- **Temps.** A 3x3 square showing high temperatures in the top-left corner, low temps in the bottom-right corner, and average temps as a diagonal running bottom-left to top-right.
- **All Day.** Similar to the `Temps` design, but using a 4x4 square. The top-left corner also contains a tile for the daily precipitation; the bottom-right corner also contains a tile for the daylight hours.
- **Smiley.** This 9x9 square is more an example of what _can_ be done, rather than something that would probably get used (but you could use it if you wanted). It shows a smiley face with high temps in the upper corners, low temps in the bottom corners, and average temps within the smiley face.

#### Ranges and Colors for Temperature, Daylight, and Precipitation

Let's take temperature for example:

```
'temperature' => [
    '-100' => ['blue', 'Blue'],
    '32' => ['green', 'Green'],
    '50' => ['yellow', 'Yellow'],
    '80' => ['red', 'Red'],
],
```

##### Ranges

Each range has a 'lowest temperature' key. So anything less than 32º is blue, 32-49º is green, 50-79º is yellow and anything 80º and up is red.

- Temperature ranges are listed as degrees
- Daylight ranges are listed as hours
- Precipitation ranges are listed as inches

##### Colors

Each range also designates the color shown on the screen as well as a color 'name'.

- The first element (shown lowercase), is the color shown on the screen. This could be a written-out color, such as 'blue'. But it could also be a hex representation, `#0000ff`.
- The second element (shown uppercase), is the name of the color shown. For instance, the yarn using this blue might be called 'Royal Blue'.
    - If the second element is not provided, we'll try to get a color name from an online database of colors.

## Using Temperature-Blanket.com URLs (Work in Progress)

In the `config/temperature-blanket-dot-com` folder, there is a file named `default.txt`. This file begins without any contents.

However, if you paste a saved URL from http://temperature-blanket.com, the saved colors and design will override those noted in the base configuration file.