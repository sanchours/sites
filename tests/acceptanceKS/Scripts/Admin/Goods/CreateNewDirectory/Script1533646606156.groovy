 /**
 * @description Создание справочника
 * @step Переход в каталог
 * @step Переход в модуль "Справочники"
 * @step Нажатие на кнопку "Добавить справочник"
 * @step Заполнение поля "Название справочника"
 * @step Нажтие на кнопку "Сохранить" 
 * @step Нажатие на кнопку "Структура"
 * @step Нажатие на кнопку "Добавить"
 * @step Заполнение поля "Название поля"
 * @step Заполнение поля "Тип отображения"
 * @step Нажатие на кнопку "Сохранить"
 * @step Нажатие на кнопку "Назад"
 * @step Нажатие на кнопку "Добавить"
 * @step Запонение всех полей указанных в структуре
 * @step Нажатие на кнопку "Сохранить"
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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import data.GoodsData as GoodsData

GoodsData DataGoods = new GoodsData()

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_directory'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_new_directory'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_directory'), '8')

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_technical_name'), '8')

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_new_directory'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/span_structure'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_create_new_field_in_structure'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_technical_name_field_in_structure'), '1')

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/input_view_type_field_in_structure'))

WebDriver driver = DriverFactory.getWebDriver()

ArrayList<WebElement> listTypeField = driver.findElements(By.cssSelector('.list-ct ul li'))

int countListTypeField = listTypeField.size()

nameField = driver.findElement(By.xpath('//div[contains(@class,\'list-ct\')]/ul/li[1]')).getText()

driver.findElement(By.xpath('//div[contains(@class,\'list-ct\')]/ul/li[1]')).click()

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_field_in_structure'), nameField)

WebUI.delay(5)

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

for (int numberField = 3; numberField <= countListTypeField; numberField++) {
    WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_create_new_field_in_structure'))

    WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/input_view_type_field_in_structure'))

    nameField = driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).getText()

    driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).click()	
	
    WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_field_in_structure'), nameField)
	
	WebUI.delay(2)

    switch (nameField) {
        case 'справочник':
            WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_additional_parametrs_in_structure'))

            driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()
			
			WebUI.delay(3)

            WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

            break
        case 'галерея':
		
			WebUI.delay(5)
		
            WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/div_additional_parametrs_in_structure'))

            driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()

			WebUI.delay(3)
			
            WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

            break
        default:
			WebUI.delay(3)
			
            WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_structure'))

            break
    }
}

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/span_back_to_directoty_items'))

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_item_edit_of directory'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_item'), DataGoods.itemname)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_technical_name_item'), DataGoods.tecnicalname)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_time'), DataGoods.time)

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/input_checkbox'), FailureHandling.STOP_ON_FAILURE)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_money'), DataGoods.money)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_fractional_numbers'), DataGoods.factonalnumbers)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_string'), DataGoods.string)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_textarea'), DataGoods.textarea)

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_in_directory'))
