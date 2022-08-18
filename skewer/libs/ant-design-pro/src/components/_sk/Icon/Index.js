import {
  UilBookAlt, UilBookOpen, UilCalendarAlt, UilCheckCircle, UilCopyAlt, UilDiary, UilEditAlt, UilExport, UilEye, UilFile,
  UilHdd, UilImport, UilInfoCircle, UilLink, UilLinkAlt, UilLinkBroken, UilNotebooks, UilPlusCircle, UilProcess, UilRedo,
  UilRefresh, UilServerNetworkAlt, UilStar, UilStopCircle, UilSync, UilTicket, UilTimes, UilTimesCircle, UilUser, UilCog
} from "@iconscout/react-unicons";
import {Icon} from "antd";
import React from "react";

export default (props) => {

  const {alias, addProps={}} = props;

  let config = {
    size: "18",
  };

  config = {
    ...config,
    ...addProps
  };

  let icon;

  switch (alias) {
    case 'icon-add':
      icon = <UilPlusCircle {...config} color="#43B755" />;
      break;
    case 'icon-edit':
      icon = <UilEditAlt {...config} color="#F4AB3D" />;
      break;
    case 'icon-delete':
      config = {...config, size: "20"};
      icon = <UilTimes {...config} color="#E9535C" />;
      // icon = <UilTrash {...config} color="#E9535C" />;
      break;
    case 'icon-arrow_link':
    case 'icon-connect':
      icon = <UilLink {...config} color="#43A4DB" />;
      break;
    case 'icon-back':
    case 'icon-cancel':
      icon = <Icon type="arrow-left" {...config} style={{color: "#F4AB3D"}} />;
      break;

    case 'icon-book':
      icon = <UilBookAlt {...config} color="#5C91E0" />;
      break;
    case 'icon-book2':
      icon = <UilDiary {...config} color="#43A4DB" />;
      break;
    case 'icon-broom':
      icon = <UilTimesCircle {...config} color="#848484" />;
      break;
    case 'icon-calendar':
      icon = <UilCalendarAlt {...config} color="#43A4DB" />;
      break;
    case 'icon-clone':
      icon = <UilCopyAlt {...config} color="#5C91E0" />;
      break;
    case 'icon-configuration':
      icon = <UilCog {...config} color="#848484" />;
      // icon = <Icon {...config} type="setting" theme="filled" style={{color: "#848484"}} />;
      break;
    case 'icon-cross':
      icon = <UilTimes {...config} color="#E9535C" />;
      break;
    case 'icon-disc':
      icon = <UilHdd {...config} color="#848484" />;
      break;
    case 'icon-hidden':
      icon = <UilInfoCircle {...config} color="#848484" />;
      break;
    case 'icon-install':
      icon = <UilImport {...config} color="#848484" />;
      break;
    case 'icon-languages':
      icon = <UilBookOpen {...config} color="#848484" />;
      break;
    case 'icon-library':
      icon = <UilNotebooks {...config} color="#9D8D64" />;
      break;
    case 'icon-link':
      icon = <UilLinkAlt {...config} color="#848484" />;
      break;
    case 'icon-linkbreak':
      icon = <UilLinkBroken {...config} color="#848484" />;
      break;
    case 'icon-recover':
      icon = <UilSync {...config} color="#5C91E0" />;
      break;
    case 'icon-reinstall':
      icon = <UilRefresh {...config} color="#926FDC" />;
      break;
    case 'icon-reload':
      icon = <UilRedo {...config} color="#43B755" />;
      break;
    case 'icon-renew':
      icon = <UilProcess {...config} color="#43A4DB" />;
      break;
    case 'icon-save':
      icon = <Icon type="save" theme="filled" {...config} style={{color: "#5C91E0"}} />;
      break;
    case 'icon-saved':
      icon = <UilCheckCircle {...config} color="#43B755" />;
      break;
    case 'icon-section':
      icon = <UilFile {...config} color="#848484" />;
      break;
    case 'icon-server':
      icon = <UilServerNetworkAlt {...config} color="#848484" />;
      break;
    case 'icon-star':
      icon = <UilStar {...config} color="#F4AB3D" />;
      break;
    // case 'icon-star-inactive':
    //   icon = <UilStopCircle {...config} />;
    //   break;
    case 'icon-stop':
      icon = <UilStopCircle {...config} color="#E9535C" />;
      break;
    case 'icon-tickets':
      icon = <UilTicket {...config} color="#43A4DB" />;
      break;
    case 'icon-upgrade':
      icon = <UilExport {...config} color="#43B755" />;
      break;
    case 'icon-user':
      icon = <UilUser {...config} color="#5C91E0" />;
      break;
    case 'icon-view':
      icon = <UilEye {...config} color="#848484" />;
      break;
    case 'icon-visible':
      icon = <UilInfoCircle {...config} color="#F4AB3D" />;
      break;
    default:
      icon = <UilPlusCircle {...config} color="#43B755" />;
  }

  return icon;

};
