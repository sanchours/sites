 /**
 * @description Вход в админку
 * @step Переход на страницу входа в админку
 * @step Вход тестового пользователя
 * @step При удачном входе - откроется интерфейс админки
 * @step При неудачном - покажется уведомление о неудачном входе и перенаправления не произойдет
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
//import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import internal.GlobalVariable as GlobalVariable
import org.openqa.selenium.Keys as Keys
import urlSite.BaseLink as BaseLink

WebUI.navigateToUrl(BaseLink.getUrlDefAdm())

WebUI.setText(findTestObject('CMS/Entrance/input_login'), 'sys')

WebUI.setText(findTestObject('CMS/Entrance/input_pass'), '123123')

WebUI.click(findTestObject('CMS/Entrance/span_entrance'))

/**WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Entrance/div_error_entrance') , 0, FailureHandling.STOP_ON_FAILURE) {
    WebUI.comment('error')

    WebUI.takeScreenshot()

    WebUI.closeBrowser()

    throw new StepErrorException('Error creating test database')
}**/

