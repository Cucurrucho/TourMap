import {precacheAndRoute, cleanupOutdatedCaches} from 'workbox-precaching';
import {setCacheNameDetails} from 'workbox-core';


setCacheNameDetails({prefix: "app.elcoop.io"});
cleanupOutdatedCaches()


self.__precacheManifest = [
    {"revision":null,"url":"/"},
].concat(self.__WB_MANIFEST || []);
precacheAndRoute(self.__precacheManifest, {});
