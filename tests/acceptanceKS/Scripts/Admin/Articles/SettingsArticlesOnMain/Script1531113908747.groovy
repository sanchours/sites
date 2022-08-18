 /**
 * @description Настройка вывода статей на главную
 * @step Открываем список верхнего меню
 * @step Выбераем главный раздел
 * @step Заходим во вкладку настройка параметров
 * @step Открываем настройку статей на главной
 * @step Заполняем количество статей 10
 * @step Сохранием изменения
 */import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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

WebUI.click(findTestObject('CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('CMS/News/CheckNewsOnMain/div_main'))

WebUI.click(findTestObject('CMS/Reviews/HeaderReviewOnTheMain/span_settings'))

WebUI.click(findTestObject('CMS/Articles/CheckArticlesOnMain/img_detal_setting_articles'))

WebUI.setText(findTestObject('CMS/Articles/CheckArticlesOnMain/input_number_articles_on_main'), '10')

WebUI.click(findTestObject('CMS/Reviews/HeaderReviewOnTheMain/button_save'))
