 /**
 * @description Создание нового отзыва
 * @step Переход в верхнее меню
 * @step Переход в раздел "Покупателям"
 * @step Преход в раздел "Отзывы"
 * @step Переход во вкладку "Отзывы"
 * @step Нажатие на кнопку "Добавить"
 * @step Нажатие на кнопку "Сохранить"
 */ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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

WebUI.click(findTestObject('CMS/Reviews/div_top_menu'))

WebUI.click(findTestObject('CMS/Reviews/div_buyer'))

WebUI.click(findTestObject('CMS/Reviews/div_comments'))

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

