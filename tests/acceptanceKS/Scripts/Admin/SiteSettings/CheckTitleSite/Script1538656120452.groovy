/**
 * @description Проверка звголовка сайта
 * @step Переходим в настройки сайта
 * @step Вводим значение в поле название сайта
 * @step Сохраняем изменения
 * @step Переходим на главную страницу
 * @step Проверяем название сайта отображаемое в закладке
 */ 
import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import data.ServSetData as ServSetData
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import urlSite.BaseLink as BaseLink

ServSetData DataServSet = new ServSetData()

WebUI.click(findTestObject('CMS/SiteSettings/span_settings_site'))

WebUI.setText(findTestObject('CMS/SiteSettings/input_site_name'), DataServSet.title)

WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/span_save'))

WebUI.navigateToUrl(BaseLink.getUrlDef())

titlesite = WebUI.getWindowTitle()

//titlesite = titlesite.substring(titlesite.length() - DataServSet.title.length() - 1, titlesite.length() -1)

if (titlesite != 'Главная – title') {
	
    throw new StepErrorException('title does not match entered')
	
}

