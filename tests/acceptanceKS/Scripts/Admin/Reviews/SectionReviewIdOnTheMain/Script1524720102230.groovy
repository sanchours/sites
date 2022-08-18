/**
 * @description Вывод отзывов на главную из раздела по ID
 * @step Нажатие на кнопку добавления раздела
 * @step Выбор типа раздела (отзывы)
 * @step Нажатие на кноку "Сохранить"
 * @step Получение ID раздела
 * @step Преход в раздел "Отзывы"(созданный )
 * @step Переход во вкладку "Отзывы"
 * @step Нажатие на кнопку "Добавить"
 * @step Заполение полей
 * @step Нажатие на кнопку "Сохранить"
 * @step Установка галочки "На главной"
 * @step Перход на "Главную" в админке
 * @step Переход в "Редактор областей"
 * @step Вывод модуля "Отзывы" на главную
 * @step Переход во вкладку "Настройка параметров"
 * @step Раскрытие детального списка параметров для отзывов
 * @step Установка парамета ID раздела отзывы
 * @step Нажатие на кнопку "Сохранить"
 */

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import data.ReviewData as ReviewData

ReviewData DataReview = new ReviewData()

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/span_edit_section'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/input_parent_section'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/li_review'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/span_save'))

WebUI.delay(5)

Result = WebUI.getText(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_number_of_section'))

//WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_number_of_section'))

WebUI.click(findTestObject('CMS/Reviews/em_comments'))

WebUI.click(findTestObject('CMS/Reviews/span_comment_edit'))

WebUI.setText(findTestObject('CMS/Reviews/input_name'), DataReview.name)

WebUI.setText(findTestObject('CMS/Reviews/input_city'), DataReview.city)

WebUI.setText(findTestObject('CMS/Reviews/input_email'), DataReview.email)

WebUI.setText(findTestObject('CMS/Reviews/input_company'), DataReview.company)

WebUI.click(findTestObject('CMS/Reviews/input_rating'))

WebUI.click(findTestObject('CMS/Reviews/li_5'))

WebUI.setText(findTestObject('CMS/Reviews/body_review_text'), DataReview.text)

WebUI.click(findTestObject('CMS/Reviews/span_save_approve'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_on_the_main'))

WebUI.click(findTestObject('CMS/Reviews/div_top_menu'))

WebUI.click(findTestObject('CMS/Reviews/ReviewOnTheMain/div_home'))

WebUI.click(findTestObject('CMS/Reviews/ReviewOnTheMain/span_region_editor'))

WebUI.doubleClick(findTestObject('CMS/Reviews/ReviewOnTheMain/img_enable'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_top_menu'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_home'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/span_settings'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/img_detail_settings_review'))

WebUI.setText(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/input_id_section_review'), Result)

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/button_save'))

