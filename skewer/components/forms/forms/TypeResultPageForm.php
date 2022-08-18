<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\components\forms\entities\FormExtraDataEntity;

/**
 * Class TypeResultPageForm.
 *
 * @property FormExtraDataEntity $extraEntity
 * @property string $text
 * @property string $link
 */
class TypeResultPageForm extends ExtraDataForm
{
    public $type;

    private $_text = '';
    private $_link;

    /** @const int Базовая результирующая */
    const RESULT_PAGE_BASE = 1;

    /** @const int Сторонняя результирующая */
    const RESULT_PAGE_EXTERNAL = 2;

    /** @const int Всплывающая результирующая */
    const RESULT_PAGE_POPUP = 3;

    protected $_extraFields = [
        'text' => 'success_answer',
        'link' => 'redirect_link',
    ];

    public function __construct(int $idForm = null, array $config = [])
    {
        parent::__construct($idForm, $config);

        if ($this->_extraEntity instanceof FormExtraDataEntity) {
            $this->type = $this->_extraEntity->form->type_result_page;
            $this->setText($this->_extraEntity->success_answer);
            $this->setLink($this->_extraEntity->redirect_link);
        } else {
            $this->type = self::RESULT_PAGE_BASE;
        }
    }

    public function rules()
    {
        return [
            [['type'], 'integer', 'max' => 255],
            [['text', 'link'], 'string', 'max' => 255],
        ];
    }

    public function getShortNameObject(): string
    {
        return 'result';
    }

    /**
     * @param string $text
     */
    public function setText(string $text = null)
    {
        $this->_text = $text;
    }

    /**
     * @return null|string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link = null)
    {
        $this->_link = $link;
    }

    /**
     * @return null|string
     */
    public function getLink()
    {
        return $this->_link;
    }

    /**
     * Форма имеет базовую результирующую страницу?
     *
     * @return bool
     */
    public function isBaseResultPage(): bool
    {
        return $this->type === self::RESULT_PAGE_BASE;
    }

    /**
     * Форма имеет стороннюю результирующую страницу?
     *
     * @return bool
     */
    public function isExternalResultPage(): bool
    {
        return $this->type === self::RESULT_PAGE_EXTERNAL;
    }

    /**
     * Форма имеет всплывающую результирующую страницу?
     *
     * @return bool
     */
    public function isPopupResultPage()
    {
        return $this->type === self::RESULT_PAGE_POPUP;
    }

    public function getFormRedirect($parseLink = false): string
    {
        $redirect = $this->link;

        if ($parseLink) {
            /*Если указана ссылка как [id раздела]*/
            if (preg_match('/^\[\d+\]$/', $this->link)) {
                $redirect = \Yii::$app->router->rewriteURL($this->link);
            }
        }

        return $redirect;
    }

    public static function getTypesResultPage()
    {
        return [
            self::RESULT_PAGE_BASE => \Yii::t('forms', 'type_result_page_base'),
            self::RESULT_PAGE_EXTERNAL => \Yii::t(
                'forms',
                'type_result_page_external'
            ),
            self::RESULT_PAGE_POPUP => \Yii::t(
                'forms',
                'type_result_page_popup'
            ),
        ];
    }
}
