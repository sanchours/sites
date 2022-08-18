import React from 'react'
import StringField from '../FormFields/StringField/Index';
import HideField from '../FormFields/HideField/Index';
import PassField from '../FormFields/PassField/Index';
import TextField from '../FormFields/TextField/Index';
import SelectField from '../FormFields/SelectField/Index';
import CheckField from '../FormFields/CheckField/Index';
import TimeField from '../FormFields/TimeField/Index';
import DateField from '../FormFields/DateField/Index';
import DateTimeField from '../FormFields/DateTimeField/Index';
import NumField from '../FormFields/NumField/Index';
import FloatField from '../FormFields/FloatField/Index';
import MoneyField from '../FormFields/MoneyField/Index';
import ShowField from '../FormFields/ShowField/Index';
import Wyswyg from '../FormFields/WyswygField/Index'
import File from '../FormFields/File/Index'
import SlideShower from '../FormFields/SlideShower/Index'
import GalleryField from '../FormFields/GalleryField/Index'
import CheckSet from '../FormFields/CheckSet/Index'
import CheckSet4Module from '../FormFields/CheckSet4Module/Index'
import MapSingleMarker from '../FormFields/MapSingleMarker/Index'
import MapListMarkers from '../FormFields/MapListMarkers/Index'

export const createFormField = (props) => {
  let instance = null;

  const {configField} = props;

  const {type, extendLibName, editable} = configField;

  switch (type) {
    case 'str':
      instance = <StringField {...props} />;
      break;
    case 'hide':
      instance = <HideField {...props} />;
      break;
    case 'pass':
      instance = <PassField {...props} />;
      break;

    case 'wyswyg':
      instance = <Wyswyg {...props} />;
      break;

    case 'text':
    case 'inherit':
    case 'text_html':
    case 'text_js':
    case 'text_css':
    case 'html':
      instance = <TextField {...props} />;
      break;

    case 'select':
    case 'multiselect':
    case 'selectimage':
    case 'paymentObject':
      instance = <SelectField {...props} />;
      break;

    case 'check':
      instance = <CheckField {...props} />;
      break;

    case 'time':
      instance = <TimeField {...props} />;
      break;

    case 'date':
      instance = <DateField {...props} />;
      break;

    case 'datetime':
      instance = <DateTimeField {...props} />;
      break;

    case 'num':
      instance = <NumField {...props} />;
      break;

    case 'float':
      instance = <FloatField {...props} />;
      break;

    case 'money':
      instance = <MoneyField {...props} />;
      break;

    case 'show':
      instance = <ShowField {...props} />;
      break;

    case 'file':
    case 'imagefile':
      instance = <File {...props} />;
      break;

    case 'specific':

      switch (extendLibName) {
        case "SlideShower":
          instance = <SlideShower {...props} />;
          break;
        case 'CheckSet':
          instance = <CheckSet {...props} />;
          break;
        case 'CheckSet4Module':
          instance = <CheckSet4Module {...props} />;
          break;
        default:
          throw new Exception('Unknown specific field`s class');
      }

      break;

    case 'gallery':
      instance = <GalleryField {...props} />;
      break;

    case 'colorselector':
      instance = <StringField {...props} />;
      break;

    case 'mapSingleMarker':
      instance = <MapSingleMarker {...props} />;
      break;

    case 'mapListMarkers':
      instance = <MapListMarkers {...props} />;
      break;

    default:
      throw new Exception('Unknown field`s class');
  }

  return instance ? instance : null;
};
