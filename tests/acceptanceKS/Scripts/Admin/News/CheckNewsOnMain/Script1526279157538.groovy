 /**
 * @description Проверка активности новой новости на главной
 * @step Проверка что новость есть в списке новостей в панели администрирования
 * @step Проверка установлена ли галочка активность, если её нет то устанавливает её
 * @step Проверка установлена ли галочка на главной, если её нет то устанавливает её 
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Переходим на главную страницу
 * @step Проверяем соответствие названия и аннонсного текста у первой новости
 * @step Возвращение окна с панелью администрирования
 * @step Переход на вкладку новостей 
 * @step Отключение галочки на главной
 * @step Возвращение окна с пользовательской частью
 * @step Обновление страницы
 * @step Проверка на отсутствие на странице названия и анонсного текста новости
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
import urlSite.BaseLink as BaseLink
import internal.GlobalVariable as GlobalVariable
import data.NewsData as NewsData

NewsData DataNews = new NewsData()

WebUI.verifyElementText(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_created_news_title_nom'), DataNews['title'])

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_news_on_main_nom'), 0, FailureHandling.OPTIONAL) == 
false) {
    WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_news_on_main_nom'))
}

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_news_is_active_nom'), 0, FailureHandling.OPTIONAL) == 
true) {
WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_news_is_active_nom'))
}

    WebUI.click(findTestObject('Site/News/NewsCheck/span_news_ediror_tab'))

    WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/a_news_page_link_nom'))
	
    WebUI.switchToWindowIndex(1)

    WebUI.navigateToUrl(BaseLink.getUrlDef())

    WebUI.verifyElementText(findTestObject('CMS/News/CheckNewsOnMain/a_new_news_on_main'), DataNews.title)

    WebUI.verifyElementText(findTestObject('Site/News/CheckNewsOnMain/div_text_the_announcment_on_main'), DataNews.аtext)

    WebUI.switchToWindowIndex(0)

    WebUI.click(findTestObject('CMS/News/CreateNewNews/span_news'))

    WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_news_on_main_nom'))

    WebUI.switchToWindowIndex(1)

    WebUI.refresh()

    WebUI.verifyTextNotPresent(DataNews.title, false)

    WebUI.verifyTextNotPresent(DataNews.аtext, false)
