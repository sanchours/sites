 /**
 * @description Добавление всех типов полей на форму
 * @step Добавление новой формы
 * @step Добавление поля типа "Выпадающий список"
 * @step Добавление поля типа "Галочка"
 * @step Добавление поля типа "Группа галочек"
 * @step Добавление поля типа "Группа переключателей"
 * @step Добавление поля типа "Загрузка файла"
 * @step Добавление поля типа "Календарь"
 * @step Добавление поля типа "Многострочное текстовое поле"
 * @step Добавление поля типа "Пароль"
 * @step Добавление поля типа "Разделитель"
 * @step Добавление поля типа "Рейтинг"
 * @step Добавление поля типа "Скрытое поле"
 * @step Добавление поля типа "Текстовое поле"
 */ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import java.lang.reflect.Field as Field
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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath

defaultParam = '1;\n2;'

WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/span_create_new_form'))

WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/span_save'))

//добавление полей
WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/span_edit_new_fields'))

WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/div_list_type_fields'))

WebDriver driver = DriverFactory.getWebDriver()

ArrayList<WebElement> listTypeField = driver.findElements(By.cssSelector('.list-ct ul li'))

int countListTypeField = listTypeField.size()

nameField = driver.findElement(By.xpath('//div[contains(@class,\'list-ct\')]/ul/li[1]')).getText()

driver.findElement(By.xpath('//div[contains(@class,\'list-ct\')]/ul/li[1]')).click()

WebUI.setText(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/input_param_title'), nameField)

WebUI.setText(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/textarea_param_default'), defaultParam)

WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/span_save_new_field'))

for (int numberField = 2; numberField <= countListTypeField; numberField++) {
	
    WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/span_edit_new_fields'))

    WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/div_list_type_fields'))

    nameField = driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).getText()

    driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).click()

    WebUI.setText(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/input_param_title'), nameField)

    switch (numberField) {
        case 3:
        case 4:
            WebUI.setText(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/textarea_param_default'), defaultParam)

            WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/span_save_new_field'))

            break
        case 10:
            WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/input_display_type'))

            WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/li_stars'))

            WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/span_save_new_field'))

            break
        default:
            WebUI.click(findTestObject('CMS/Form/CreateAllTypesFieldOnForm/span_save_new_field'))

            break
    }
}
