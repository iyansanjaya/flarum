import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import ItemList from 'flarum/common/utils/ItemList';
import Mithril from 'mithril';
type AwsRegion = {
    value: string;
    label: string;
};
export default class S3SettingsPage extends ExtensionPage {
    s3SetByEnv: boolean;
    settingPrefix: string;
    awsRegions: AwsRegion[];
    oninit(vnode: Mithril.Vnode): void;
    content(): JSX.Element;
    settingsItems(): ItemList<Mithril.Children>;
    generalItems(): ItemList<Mithril.Children>;
    s3Items(): ItemList<Mithril.Children>;
    s3CompatibleItems(): ItemList<Mithril.Children>;
    revManifestItems(): ItemList<Mithril.Children>;
}
export {};
