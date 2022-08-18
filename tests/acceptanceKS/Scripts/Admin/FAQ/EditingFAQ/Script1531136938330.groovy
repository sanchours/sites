 /**
 * @description Проверка редактирования вопроса и ответа в ВИО
 * @step Проверка соответствия вопроса и ответа у первого вио в списке
 * @step Переход в первый ВИО
 * @step Заполнение полей Вопрос и Ответ
 * @step Сохранение ВИО
 * @step Проверка соответствия вопроса и ответа у первого вио в списке
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

DataFAQ.setChangeData()

WebUI.doubleClick(findTestObject('CMS/FAQ/CreateFAQ/div_first_faq_list'))

WebUI.setText(findTestObject('CMS/FAQ/CreateFAQ/textarea_question_faq'), DataFAQ.question)

WebUI.setText(findTestObject('CMS/FAQ/CreateFAQ/body_answer_faq'), DataFAQ.answer)

WebUI.click(findTestObject('CMS/FAQ/CreateFAQ/span_save_faq'))

WebUI.verifyElementText(findTestObject('CMS/FAQ/CreateFAQ/td_question_faq'), DataFAQ.question)

WebUI.verifyElementText(findTestObject('CMS/FAQ/CreateFAQ/td_answer_faq'), DataFAQ.answer)
