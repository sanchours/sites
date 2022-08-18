 /**
 * @description Проверка перехода в ВИО + проверка псевдонима
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Url1 присваиваем значение урла открытой страницы 
 * @step Переходим в первую статью
 * @step Url2 присваиваем значение урла открытой страницы 
 * @step Url присваиваем значение разницы Url1 и Url2
 * @step Сравиваем URL и Urlinput, в случае несоответствия выдается ошибка
 */ import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
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
import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import internal.GlobalVariable as GlobalVariable
import data.FAQData as FAQData

FAQData DataFAQ = new FAQData()

UrlInput = DataFAQ.alias

WebUI.click(findTestObject('CMS/FAQ/CheckFAQ/span_faq_editor_tab'))

WebUI.click(findTestObject('CMS/FAQ/CheckFAQ/a_faq_page_link'))

WebUI.switchToWindowIndex(1)

Url1 = WebUI.getUrl()

WebUI.click(findTestObject('Site/FAQ/CheckFAQ/a_first_faq'))

Url2 = WebUI.getUrl()

Url2 = Url2.substring(Url1.length())

if (Url2 != UrlInput) {
    throw new StepErrorException('Адрес результирующей страницы неверен!')
}

