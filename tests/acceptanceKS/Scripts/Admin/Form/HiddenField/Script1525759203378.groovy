 /**
 * @description Скрытое поле для формы
 * @step Переход в модуль форм
 * @step Переход в настройки формы
 * @step Установка галочки скрытое поле
 * @step Переход на страницу с формой на полльзовательской части
 * @step Заполнение скрытого поля
 * @step Заполненение обязательных полей на форме
 * @step Отправка формы
 * @step Проверка наличия сообщения об ошибке валидации
 */ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import org.openqa.selenium.WebDriver as WebDriver
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
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW

WebUI.click(findTestObject('CMS/Form/HiddenField/div_control_panel'))

WebUI.click(findTestObject('CMS/Form/HiddenField/div_forms'))

WebUI.doubleClick(findTestObject('CMS/Form/HiddenField/div_form_feedback'))

WebUI.delay(5)

WebUI.click(findTestObject('CMS/Form/HiddenField/span_form_settings'))

WebUI.click(findTestObject('CMS/Form/HiddenField/input_hidden field'))

WebUI.click(findTestObject('CMS/Form/HiddenField/span_save'))

WebUI.click(findTestObject('CMS/Form/CheckboxLicenseAgreement/div_section_list'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_top_menu'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

WebUI.click(findTestObject('Site/ReviewCheck/a_review_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.modifyObjectProperty(findTestObject('CMS/Form/HiddenField/input_hidden_field_on_page'), '1', '2', '3', false)

WebUI.delay(10)

WebDriver driver = DriverFactory.getWebDriver()

WebElement HiddenField = driver.findElement(By.xpath('//input[contains(@class,\'form__cptch_country\')]'))

WebUI.executeJavaScript('arguments[0].value = "123"', Arrays.asList(HiddenField))

WebUI.setText(findTestObject('CMS/Form/HiddenField/textarea_text'), '2')

WebUI.setText(findTestObject('CMS/Form/HiddenField/input_email'), 'test@test.te')

WebUI.click(findTestObject('Site/SendReviewOnTheDetail/label_private_policy'))

WebUI.click(findTestObject('CMS/Form/HiddenField/button_send_form'))

WebUI.verifyElementVisible(findTestObject('CMS/Form/HiddenField/li_error_valid'))

