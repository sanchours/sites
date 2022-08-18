 /**
 * @description Создание новой новости
 * @step Переход во вкладку "Новости"
 * @step Нажатие на кнопку "Добавить новость"
 * @step Заполнение полей Название, Анонс, Полный текст, Источник и Псевдоним
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
import data.NewsData as NewsData

NewsData DataNews = new NewsData()

WebUI.click(findTestObject('CMS/News/CreateNewNews/span_news'))

WebUI.click(findTestObject('CMS/News/CreateNewNews/span_new_news'))

WebUI.setText(findTestObject('CMS/News/CreateNewNews/input_title_news'), DataNews.title)

WebUI.setText(findTestObject('CMS/News/CreateNewNews/body_announcement_news'), DataNews.аtext)

WebUI.setText(findTestObject('CMS/News/CreateNewNews/body_full_text_news'), DataNews.ftext)

WebUI.setText(findTestObject('CMS/News/CreateAndCheckAliasNews/input_news_alias'), DataNews.alias)

WebUI.setText(findTestObject('CMS/News/CreateAndCheckSourceNews/input_source_link_news'), DataNews.link)

WebUI.click(findTestObject('CMS/News/CreateNewNews/span_save'))

