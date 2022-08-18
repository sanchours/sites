/** @description Проверка фильтра отзывов
 * @step Добавление 3х отзывов
 * @step Проверка статуса "Новый" у 3х отзывов
 * @step Включение статуса "Новый" в фильтре
 * @step Проверка статуса "Новый" у 3х отзывов
 * @step Установка статуса "Одобрен" у 3х отзывов
 * @step Включение статуса "Одобрен" в фильтре
 * @step Проверка статуса "Одобрен" у 3х отзывов
 * @step Установка статуса "Отклонен" у 3х отзывов
 * @step Включение статуса "Отклонен" в фильтре
 * @step Проверка статуса "Отклонен" у 3х отзывов
 * @step Установка статуса "Одобрен" у 1 отзыва
 * @step Добавление отзыва со статусом "Новый"
 * @step Включение статуса "Все" в фильтре
 * @step Проверка статуса "Новый" у 1го отзыва
 * @step Проверка статуса "Одобрен" у 2го отзыва
 * @step Проверка статуса "Отклонен" у 3го отзыва
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

for (int i = 0; i < 3; i++) {
    WebUI.click(findTestObject('CMS/Reviews/span_comment_edit'))

    /* WebUI.setText(findTestObject('CMS/Reviews/input_name'), DataReview.name)

    WebUI.setText(findTestObject('CMS/Reviews/input_city'), DataReview.city)

    WebUI.setText(findTestObject('CMS/Reviews/input_email'), DataReview.email)

    WebUI.setText(findTestObject('CMS/Reviews/input_company'), DataReview.company)

    WebUI.click(findTestObject('CMS/Reviews/input_rating'))

    WebUI.click(findTestObject('CMS/Reviews/li_5'))

    WebUI.setText(findTestObject('CMS/Reviews/body_review_text'), DataReview.text)*/
    WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/span_save_review'))
}

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n1'), 'Новый')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n2'), 'Новый')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n3'), 'Новый')

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/em_filter'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/a_new'))

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n1'), 'Новый')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n2'), 'Новый')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n3'), 'Новый')

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/img_appruve_1_review'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/img_appruve_2_review'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/img_appruve_3_review'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/em_filter'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/a_appruve'))

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n1'), 'Одобрен')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n2'), 'Одобрен')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n3'), 'Одобрен')

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/img_reject_1_review'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/img_reject_2_review'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/img_reject_3_review'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/em_filter'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/a_reject'))

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n1'), 'Отклонен')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n2'), 'Отклонен')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n3'), 'Отклонен')

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/img_appruve_1_review'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/em_filter'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/a_all'))

WebUI.click(findTestObject('CMS/Reviews/span_comment_edit'))

WebUI.click(findTestObject('CMS/Reviews/ReviewFilter/span_save_review'))

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n1'), 'Новый')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n2'), 'Одобрен')

WebUI.verifyElementText(findTestObject('CMS/Reviews/ReviewFilter/div_review_status_n3'), 'Отклонен')

