package de.fehrprice.crypto;

import java.nio.charset.StandardCharsets;

public class Conv {
	
	/**
	 * Convert input String to byte array using UTF_8 charset.
	 * @param plaintext
	 * @return
	 */
	public static byte[] plaintextToByteArray(String plaintext) {
		return plaintext.getBytes(StandardCharsets.UTF_8);
	}
	
	public static String toPlaintext(byte[] res_array) {
		return new String(res_array, StandardCharsets.UTF_8);
	}

	/**
	 * Extend byte array to a fixed length by appending zero bytes
	 * @param finalLength
	 * @param input
	 * @return
	 */
	public static byte[] extendWithZeroBytes(int finalLength, byte[] input) {
		if (input.length >= finalLength) {
			return input;
		}
		byte[] res = new byte[finalLength];
		System.arraycopy(input, 0, res, 0, input.length);
		for (int i = input.length; i < finalLength; i++) {
			res[i] = 0;
		}
		return res;
	}

	/**
	 * Extend byte array at beginning (low index) to a fixed length by prepending zero bytes
	 * @param finalLength
	 * @param input
	 * @return
	 */
	public static byte[] prependWithZeroBytes(int finalLength, byte[] input) {
		if (input.length >= finalLength) {
			return input;
		}
		byte[] res = new byte[finalLength];
		System.arraycopy(input, 0, res, finalLength - input.length, input.length);
		for (int i = 0; i < finalLength - input.length; i++) {
			res[i] = 0;
		}
		return res;
	}

	/**
	 * Convert hex string to Big Endian byte array. Every two chars is considered a hex string representation of one byte.
	 * First two chars in hexstring will be the byte at position 0. Big Endian ordering if input string is considered to be one number.
	 * Will throw NumberFormatException for invalid input (odd length or invalid character)
	 * @param hexstring
	 * @return
	 */
	public static byte[] toByteArray(String hexstring) {
		if (hexstring.length() % 2 == 1) {
			throw new NumberFormatException("hex string should have even length");
		}
		byte[] bytes = new byte[hexstring.length()/2];
		for (int i = 0; i < bytes.length; i ++) {
			String sub = hexstring.substring(i*2, i*2 + 2);
			// Integer.parseInt() will throw NumberFormatException on invalid input
			bytes[i] = (byte)(Integer.parseInt(sub, 16)& 0xff);
		}
		return bytes;
	}

	/**
	 * Lengthen byte array to specified length by prepending 0 bytes at end.
	 * NumberFormatException if array is already too big. 
	 * @param newLen
	 * @param oldarray
	 * @return
	 */
	public static byte[] fixByteArrayLength(int newLen, byte[] oldarray) {
		if (oldarray.length > newLen) {
			throw new NumberFormatException(" source array too big. Should be <= " + newLen + " bytes");
		}
		byte newArray[] = new byte[newLen];
		System.arraycopy(oldarray, 0, newArray, 0, oldarray.length);
		for (int i = oldarray.length; i < newLen; i++) {
			newArray[i] = 0;
		}
		return newArray;
	}

	/**
	 * Format byte array as Hex String - on byte yields 2 chars.
	 * Big Endian order: First byte will be firts 2 chars of String
	 * @param bytes
	 * @return
	 */
	public static String toString(byte[] bytes) {
		StringBuffer buf = new StringBuffer();
		for (int i = 0; i < bytes.length; i++) {
			buf.append(String.format("%02x", bytes[i]));
		}
		return buf.toString();
	}

}
