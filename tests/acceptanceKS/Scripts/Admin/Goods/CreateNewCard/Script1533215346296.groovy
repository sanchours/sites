 /**
 * @description Создание новой карточки
 * @step Переход в интерфейс "Каталог"
 * @step Переход в "Редактор карточек"
 * @step Добавление новой карточки
 * @step Нажатие на кнопку "Добавить карточку"
 * @step Заполенение поля "Имя карточки"
 * @step Заполнение поля "Техническое имя"
 * @step Заполнение поля "Базовая карточка"
 * @step Нажатие на кнопку "Сохранить"
 * @step Нажатие на кнопку "Добавить поле"
 * @step Заполнение поля "Имя поля"
 * @step Заполнение поля "Техническое имя"
 * @step Заполнение поля "Тип отображения"
 * @step Заполнение поля "Виджет"
 * @step Заполнение поля "Сущность"
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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_card_editor'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_card_create'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewCard/input_name_card'), '1')

WebUI.setText(findTestObject('CMS/Goods/CreateNewCard/input_technical_name'), '1')

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_base_card_list'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_base_card_of_catalog_in_the_list'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/button_save_new_card'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_create_new_field'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_view_type_list'))

WebDriver driver = DriverFactory.getWebDriver()

ArrayList<WebElement> listTypeField = driver.findElements(By.cssSelector('.list-ct ul li'))

int countListTypeField = listTypeField.size()

nameField = driver.findElement(By.xpath('//div[contains(@class,\'list-ct\')]/ul/li[1]')).getText()

driver.findElement(By.xpath('//div[contains(@class,\'list-ct\')]/ul/li[1]')).click()

WebUI.setText(findTestObject('CMS/Goods/CreateNewCard/input_name_field'), nameField)

WebUI.setText(findTestObject('CMS/Goods/CreateNewCard/input_technical_name field'), '1')

//WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_essence_list'))

WebUI.delay(3)

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/button_save_new_field'))

WebUI.delay(3)

for (int numberField = 2; numberField <= countListTypeField; numberField++) {
    WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_create_new_field'))

    WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_view_type_list'))

    nameField = driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).getText()

    driver.findElement(By.xpath(('//div[contains(@class,\'list-ct\')]/ul/li[' + numberField) + ']')).click()

    WebUI.setText(findTestObject('CMS/Goods/CreateNewCard/input_name_field'), nameField)

    WebUI.setText(findTestObject('CMS/Goods/CreateNewCard/input_technical_name field'), '3')

    switch (nameField) {
        case 'справочник':
            WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_entity_field'))

            driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()
			
			WebUI.delay(2)

            WebUI.click(findTestObject('CMS/Goods/CreateNewCard/button_save_new_field'))
			
			WebUI.delay(2)
//			WebUI.click(findTestObject('Object Repository/CMS/Goods/CreateNewCard/button_back_to_list'))
//		
//		    WebUI.click(findTestObject('Object Repository/CMS/Goods/CreateNewCard/span_accept_back_to_list'))
//		
            break
        case 'справочник (мультисписок)':
            WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_entity_field'))

            driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()
			
			WebUI.delay(2)

            WebUI.click(findTestObject('CMS/Goods/CreateNewCard/button_save_new_field'))
			
			WebUI.delay(2)
//			WebUI.click(findTestObject('Object Repository/CMS/Goods/CreateNewCard/button_back_to_list'))
		
//			WebUI.click(findTestObject('Object Repository/CMS/Goods/CreateNewCard/span_accept_back_to_list'))
		
            break
			
		case 'коллекция (мультисписок)':
			WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_entity_field'))

			driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()
			
			WebUI.delay(2)

			WebUI.click(findTestObject('CMS/Goods/CreateNewCard/button_save_new_field'))
			
			WebUI.delay(2)

//			WebUI.click(findTestObject('Object Repository/CMS/Goods/CreateNewCard/button_back_to_list'))
			
//			WebUI.click(findTestObject('Object Repository/CMS/Goods/CreateNewCard/span_accept_back_to_list'))
		
			break
		case 'коллекция':
			WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_entity_field'))

			driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()
			
			WebUI.delay(2)

			WebUI.click(findTestObject('CMS/Goods/CreateNewCard/button_save_new_field'))
			
			WebUI.delay(2)

	//		WebUI.click(findTestObject('Object Repository/CMS/Goods/CreateNewCard/button_back_to_list'))
		
	//		WebUI.click(findTestObject('Object Repository/CMS/Goods/CreateNewCard/span_accept_back_to_list'))
		
			break
			case 'справочник (изображение)':
			WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_entity_field'))

			driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()
			
			WebUI.delay(2)

			WebUI.click(findTestObject('CMS/Goods/CreateNewCard/button_save_new_field'))
			
			WebUI.delay(2)
			
			break
			case 'справочник (мультисписок, изображение)':
			WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_entity_field'))

			driver.findElement(By.xpath('//body/div[last()]/div[contains(@class,\'list-ct\')][1]/ul/li[1]')).click()
			
			WebUI.delay(2)

			WebUI.click(findTestObject('CMS/Goods/CreateNewCard/button_save_new_field'))
			
			WebUI.delay(2)
			
			break
			
        default:
		
			WebUI.delay(2)
		
            WebUI.click(findTestObject('CMS/Goods/CreateNewCard/button_save_new_field'))
			
			WebUI.delay(2)

            break
    }
    
}

