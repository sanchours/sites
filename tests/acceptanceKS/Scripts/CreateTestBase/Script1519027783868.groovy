/**
 * @description Создание тестовой базы
 * @step Переход на ..your_domain/test-accept
 * @step Проверка на создание тестовой базы
 * @step При удачном создании тестовой произойдет редирект на домашнюю страницу
 * @step При неудачном - останется на странице ..your_domain/test-accept с ошибкой
 */
import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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
import urlSite.BaseLink as BaseLink

WebUI.navigateToUrl(BaseLink.transitionUrlTest())

urlThis = WebUI.getUrl()

/*if (urlThis != (BaseLink.getUrlDef() + '/')) {
	throw new com.kms.katalon.core.exception.StepFailedException('Not correct IP-address',FailureHandling.STOP_ON_FAILURE)
	WebUI.callTestCase(findTestCase('CloseBrowser'))
	return;
	
}*/
