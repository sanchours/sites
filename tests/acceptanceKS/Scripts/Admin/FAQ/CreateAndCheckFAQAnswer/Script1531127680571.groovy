 /**
 * @description Создание вио только с ответом
 * @step Переход во вкладку "Вопросы и ответы "
 * @step Нажатие на кнопку "Добавить"
 * @step Заполнение поля Ответ
 * @step Нажатие на кнопку "Сохранить"
 * @step Проверка соответствия поля ответ в админке
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
import data.FAQData as FAQData

FAQData DataFAQ = new FAQData()

WebUI.click(findTestObject('CMS/FAQ/CreateFAQ/span_ faq'))

WebUI.click(findTestObject('CMS/FAQ/CreateFAQ/span_new_faq'))

WebUI.setText(findTestObject('CMS/FAQ/CreateFAQ/body_answer_faq'), DataFAQ.answer)

WebUI.click(findTestObject('CMS/FAQ/CreateFAQ/span_save_faq'))

WebUI.verifyElementText(findTestObject('CMS/FAQ/CreateFAQ/td_answer_faq'), DataFAQ.answer)

