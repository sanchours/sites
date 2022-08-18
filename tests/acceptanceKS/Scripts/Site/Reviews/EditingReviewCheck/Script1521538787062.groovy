/**
 * @description Проверка отредактированного отзыва
 * @step Переход на на вкладку "Редактор" в админке
 * @step Переход на страницу "Отзывы"(нажатие на ссылку)
 * @step Активизация новой вкладки браузера
 * @step Проверка заданного значения поля "ФИО"
 * @step Проверка заданного значения поля "Компания"
 * @step Проверка заданного значения поля "Город"
 * @step Проверка заданного значения поля "Текст отзыва"
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

ReviewData DataReview = new ReviewData();
DataReview.setChangeData();

WebUI.click(findTestObject('Site/ReviewCheck/span_review_ediror_tab'))

WebUI.click(findTestObject('Site/ReviewCheck/a_review_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyElementText(findTestObject('Site/ReviewCheck/div_name'), DataReview.name)

WebUI.verifyElementText(findTestObject('Site/ReviewCheck/div_company'), DataReview.company)

WebUI.verifyElementText(findTestObject('Site/ReviewCheck/div_city'), DataReview.city)

WebUI.verifyElementText(findTestObject('Site/ReviewCheck/div_text_review'), DataReview.text)

