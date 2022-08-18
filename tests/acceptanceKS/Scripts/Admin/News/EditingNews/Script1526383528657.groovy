 /**
 * @description Проверка редактирования новости
 * @step Проверка соответствия названия первой новости списка с названием добавленной новости
 * @step Проверка установлена ли галочка активность, если её нет то устанавливает её
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Проверяем соответствие названия и аннонсного текста у первой новости
 * @step Переходим по первой новости
 * @step Проверяем соответствие полного текста новости
 * @step Возвращение окна с панелью администрирования
 * @step Переход на вкладку новостей 
 * @step Переход в первую новость
 * @step Заполнение полей Новость, Анонс и Полный текст
 * @step Сохранение новости 
 * @step Проверка установлена ли галочка активность, если её нет то устанавливает её 
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Проверяем соответствие измененного названия и аннонсного текста у первой новости
 * @step Переходим по первой новости
 * @step Проверяем соответствие измененного полного текста новости 
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
import data.NewsData as NewsData

NewsData DataNews = new NewsData()

WebUI.verifyElementText(findTestObject('CMS/News/CheckNews/div_created_news_title'), DataNews.title)

if (WebUI.verifyElementChecked(findTestObject('CMS/News/CheckNews/div_news_is_active'), 1, FailureHandling.OPTIONAL) == 
true) { 
    WebUI.click(findTestObject('CMS/News/CheckNews/div_news_is_active'))
	}

    WebUI.click(findTestObject('Site/News/NewsCheck/span_news_ediror_tab'))

    WebUI.click(findTestObject('Site/News/NewsCheck/a_news_page_link'))

    WebUI.switchToWindowIndex(1)

    WebUI.verifyElementText(findTestObject('Site/News/NewsCheck/a_title_news'), DataNews.title)

    WebUI.verifyElementText(findTestObject('Site/News/NewsCheck/div_text_of_the_announcement'), DataNews.аtext)

    WebUI.click(findTestObject('Site/News/NewsCheck/a_title_news'))

    WebUI.verifyElementText(findTestObject('Site/News/NewsCheck/div_text_news'), DataNews.ftext)

DataNews.setChangeData()

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('CMS/News/CreateNewNews/span_news'))

WebUI.doubleClick(findTestObject('CMS/News/CheckNews/div_first_news_in_the_list'))

WebUI.setText(findTestObject('CMS/News/CreateNewNews/input_title_news'), DataNews.title)

WebUI.setText(findTestObject('CMS/News/CreateNewNews/body_announcement_news'), DataNews.аtext)

WebUI.setText(findTestObject('CMS/News/CreateNewNews/body_full_text_news'), DataNews.ftext)

WebUI.click(findTestObject('CMS/News/CreateNewNews/span_save'))

WebUI.verifyElementText(findTestObject('CMS/News/CheckNews/div_created_news_title'), DataNews['title'])

if (WebUI.verifyElementChecked(findTestObject('CMS/News/CheckNews/div_news_is_active'), 1, FailureHandling.OPTIONAL) == 
true) {
	WebUI.click(findTestObject('CMS/News/CheckNews/div_news_is_active'))
	}

    WebUI.click(findTestObject('Site/News/NewsCheck/span_news_ediror_tab'))

    WebUI.click(findTestObject('Site/News/NewsCheck/a_news_page_link'))

    WebUI.switchToWindowIndex(2)

    WebUI.verifyElementText(findTestObject('Site/News/NewsCheck/a_title_news'), DataNews.title)

    WebUI.verifyElementText(findTestObject('Site/News/NewsCheck/div_text_of_the_announcement'), DataNews.аtext)

    WebUI.click(findTestObject('Site/News/NewsCheck/a_title_news'))

    WebUI.verifyElementText(findTestObject('Site/News/NewsCheck/div_text_news'), DataNews.ftext)

