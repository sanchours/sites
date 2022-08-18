const path = require('path');
const fs = require('fs');
const RemovePlugin = require('remove-files-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const moduleList = fs.readdirSync('skewer/build/Page/');
const entries = {};

moduleList.forEach((module) => {
  try {
    fs.accessSync(
      `./skewer/build/Page/${module}/web/react/app.js`,
      fs.constants.R_OK
    );
    console.error(`Обнаружен React в ${module}. Собираю.`);
    entries[module] = `/skewer/build/Page/${module}/web/react/app.js`;
  } catch (err) {
    console.error(`Модуль ${module} без React`);
  }
});

module.exports = (env) => {
  if (env.optimize) {
    console.log('Оптимизация изображений...');

    return {
      entry: entries,
      mode: env.mode,
      output: {
        path: path.resolve(__dirname, ''),
        filename: 'skewer/build/Page/[name]/web/js/script.compile.js',
      },
      performance: {
        hints: false,
      },
      module: {
        rules: [
          {
            test: /\.(js|jsx)$/,
            exclude: /(node_modules)/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env', '@babel/preset-react'],
              },
            },
            resolve: {
              extensions: ['.js', '.jsx'],
            },
          },
          {
            test: /\.(gif|png|jpe?g|svg)$/i,
            use: [
              {
                loader: 'file-loader',
                options: {
                  outputPath: 'web/react_images',
                  publicPath: '/react_images/',
                  name: '[name]-[contenthash].[ext]',
                },
              },
              {
                loader: 'image-webpack-loader',
                options: {
                  mozjpeg: {
                    progressive: true,
                    quality: 85,
                  },
                  optipng: {
                    enabled: false,
                  },
                  pngquant: {
                    speed: 5,
                    strip: true,
                    quality: [0.2, 0.3],
                  },
                  gifsicle: {
                    enabled: false,
                  },
                },
              },
            ],
          },
          {
            test: /\.css$/i,
            use: ['style-loader', 'css-loader'],
          },
          {
            test: /\.s[ac]ss$/i,
            use: [
              MiniCssExtractPlugin.loader,
              {
                loader: 'css-loader',
                options: {
                  url: false,
                },
              },
              'sass-loader',
            ],
          },
        ],
      },
      plugins: [
        new MiniCssExtractPlugin({
          filename: 'skewer/build/Page/[name]/web/css/style.compile.css',
        }),
        new RemovePlugin({
          before: {
            include: ['./web/react_images'],
          },
        }),
      ],
    };
  }

  return {
    entry: entries,
    mode: env.mode,
    output: {
      path: path.resolve(__dirname, ''),
      filename: 'skewer/build/Page/[name]/web/js/script.compile.js',
    },
    performance: {
      hints: false,
    },
    module: {
      rules: [
        {
          test: /\.(js|jsx)$/,
          exclude: /(node_modules)/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env', '@babel/preset-react'],
            },
          },
          resolve: {
            extensions: ['.js', '.jsx'],
          },
        },
        {
          test: /\.(png|jpe?g|svg)$/i,
          loader: 'file-loader',
          options: {
            outputPath: 'web/react_images',
            publicPath: '/react_images/',
            name: '[name]-[contenthash].[ext]',
          },
        },
        {
          test: /\.css$/i,
          use: ['style-loader', 'css-loader'],
        },
        {
          test: /\.s[ac]ss$/i,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                url: false,
              },
            },
            'sass-loader',
          ],
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: 'skewer/build/Page/[name]/web/css/style.compile.css',
      }),
      new RemovePlugin({
        before: {
          include: ['./web/react_images'],
        },
      }),
    ],
  };
};
