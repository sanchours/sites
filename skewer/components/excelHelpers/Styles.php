<?php

namespace skewer\components\excelHelpers;

class Styles
{
    /**
     * Заголовок.
     *
     * @var array
     */
    public static $HEADER = [
        'fill' => [
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'color' => [
                'rgb' => 'CFCFCF',
            ],
        ],
        'alignment' => [
            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ],
        'font' => [
            'bold' => true,
        ],
    ];

    /**
     * Зелёная заливка.
     *
     * @var array
     */
    public static $GREEN = [
        'fill' => [
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'color' => [
                'rgb' => '98FB98',
            ],
        ],
    ];

    /**
     * Красная заливка.
     *
     * @var array
     */
    public static $RED = [
        'fill' => [
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'color' => [
                'rgb' => 'FF7373',
            ],
        ],
    ];

    /**
     * Граница вокруг всех ящеек со всех всех сторон в выбранной области.
     *
     * @var array
     */
    public static $BORDER_ALLBORDERS = [
          'borders' => [
              'allborders' => [
                  'style' => \PHPExcel_Style_Border::BORDER_THIN,
                  'color' => ['rgb' => '000000'],
              ],
          ],
    ];

    /**
     * Граница вокруг выбранной области
     * _______
     * |x x x|
     * |x x x|
     * |_____|.
     *
     * @var array
     */
    public static $BORDER_OUTLINE = [
        'borders' => [
            'outline' => [
                'style' => \PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    /**
     * Верхняя граница.
     *
     * @var array
     */
    public static $BORDER_TOP = [
        'borders' => [
            'top' => [
                'style' => \PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    /**
     * Левая граница.
     *
     * @var array
     */
    public static $BORDER_LEFT = [
        'borders' => [
            'left' => [
                'style' => \PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    /**
     * Нижняя граница.
     *
     * @var array
     */
    public static $BORDER_BOTTOM = [
        'borders' => [
            'bottom' => [
                'style' => \PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    /**
     * Правая граница.
     *
     * @var array
     */
    public static $BORDER_RIGHT = [
        'borders' => [
            'right' => [
                'style' => \PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    /**
     * Жирное начертание.
     *
     * @var array
     */
    public static $FONT_BOLD = [
      'font' => [
          'bold' => true,
      ],
    ];

    /**
     * Курсивное начертание.
     *
     * @var array
     */
    public static $FONT_ITALIC = [
        'font' => [
            'italic' => true,
        ],
    ];

    /**
     * Подчеркивание.
     *
     * @var array
     */
    public static $FONT_UNDERLINE = [
        'font' => [
            'underline' => true,
        ],
    ];

    /**
     * Шрифт по умолчанию.
     *
     * @var array
     */
    public static $FONT_DEFAULT = [
        'font' => [
            'underline' => false,
            'bold' => false,
            'italic' => false,
        ],
    ];
}
