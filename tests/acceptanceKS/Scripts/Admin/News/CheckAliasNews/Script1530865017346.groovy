/**
 * @description Проверка новости с псевдонимом
 * @step Urlinput присваиваем значение заполненого нами псевдонима 
 * @step Проверка установлена ли галочка активность, если её нет то устанавливает её
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Url1 присваиваем значение урла открытой страницы 
 * @step Переходим в первую новость
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
import data.NewsData as NewsData

NewsData DataNews = new NewsData()

UrlInput = DataNews.alias

if (WebUI.verifyElementChecked(findTestObject('CMS/News/CheckNews/div_news_is_active'), 1, FailureHandling.OPTIONAL) ==
true) {
	WebUI.click(findTestObject('CMS/News/CheckNews/div_news_is_active'))
	}
	
WebUI.click(findTestObject('Site/News/NewsCheck/span_news_ediror_tab'))

WebUI.click(findTestObject('Site/News/NewsCheck/a_news_page_link'))

WebUI.switchToWindowIndex(1)

Url1 = WebUI.getUrl()

WebUI.click(findTestObject('Site/News/NewsCheck/a_title_news'))

Url2 = WebUI.getUrl()

Url2 = Url2.substring(Url1.length(), )

if (Url2 != UrlInput) {
    throw new com.kms.katalon.core.exception.StepErrorException('Адрес результирующей страницы неверен!')
}

