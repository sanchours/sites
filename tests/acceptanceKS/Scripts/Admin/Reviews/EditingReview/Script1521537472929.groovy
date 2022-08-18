/**
 * @description Редактирование отзыва
 * @step Нажатие на иконку "Редактировать"
 * @step Иземенение значнеия поля "ФИО"
 * @step Иземенение значнеия поля "Город"
 * @step Иземенение значнеия поля "Компания"
 * @step Иземенение значнеия поля "email"
 * @step Иземенение значнеия поля "Текст отзыва"
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

ReviewData DataReview = new ReviewData();
DataReview.setChangeData();

WebUI.click(findTestObject('CMS/Reviews/EditingReview/img_editing'))

WebUI.setText(findTestObject('CMS/Reviews/input_name'), DataReview.name)

WebUI.setText(findTestObject('CMS/Reviews/input_city'), DataReview.city)

WebUI.setText(findTestObject('CMS/Reviews/input_email'), DataReview.email)

WebUI.setText(findTestObject('CMS/Reviews/input_company'), DataReview.company)

WebUI.click(findTestObject('CMS/Reviews/input_rating'))

WebUI.click(findTestObject('CMS/Reviews/li_5'))

WebUI.setText(findTestObject('CMS/Reviews/body_review_text'), DataReview.text)

WebUI.click(findTestObject('CMS/Reviews/EditingReview/span_save'))

