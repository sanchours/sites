/**
 * @description Отправка отзыва с детальной товара
 * @step Переход на деталльную товара
 * @step Переход в таб "Отзывы"
 * @step Заполненеие поля "Имя"
 * @step Заполненеие поля "Email"
 * @step Заполненеие поля "Город"
 * @step Заполненеие поля "Текст отзыва"
 * @step Заполненеие поля "Название компании"
 * @step Установка галочки согласия с политикой конфиденциальности
 * @step Нажатие на кнопку "Отправить"
 * @step Проверка наличия результирующей
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

WebUI.click(findTestObject('Site/SendReviewOnTheDetail/a_goods_detail'))

WebUI.click(findTestObject('Site/SendReviewOnTheDetail/a_review_on_detail'))

WebUI.setText(findTestObject('Site/SendReviewOnTheDetail/input_name'), DataReview.name)

WebUI.setText(findTestObject('Site/SendReviewOnTheDetail/input_city'), DataReview.city)

WebUI.setText(findTestObject('Site/SendReviewOnTheDetail/input_email'), DataReview.email)

WebUI.setText(findTestObject('Site/SendReviewOnTheDetail/textarea_content'), DataReview.text)

WebUI.setText(findTestObject('Site/SendReviewOnTheDetail/input_company'), DataReview.company)

WebUI.click(findTestObject('Site/SendReviewOnTheDetail/label_private_policy'), FailureHandling.STOP_ON_FAILURE)

WebUI.click(findTestObject('Site/SendReviewOnTheDetail/button_send_form'))

WebUI.verifyElementVisible(findTestObject('Site/SendReviewOnTheDetail/div_response'))

