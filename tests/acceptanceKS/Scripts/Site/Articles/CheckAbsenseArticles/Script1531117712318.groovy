 /**
 * @description Проверка удаление статьи на странице статей 
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части
 * @step Проверка имени и анонсного текста на странице статей
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
import data.ArticlesData as ArticlesData

ArticlesData DataArticles = new ArticlesData()

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/span_articles_editor_tab'))

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/a_articles_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyTextNotPresent(DataArticles['title'], false)

WebUI.verifyTextNotPresent(DataArticles['аtext'], false)

